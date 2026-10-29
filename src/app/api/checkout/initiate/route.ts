import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { createRazorpayOrder } from "@/lib/razorpay";
import { generateOrderNumber, generateSecureToken, sha256Hash } from "@/lib/crypto";
import { sendMetaServerEvent } from "@/lib/meta-tracking";

export async function POST(request: Request) {
  try {
    const body = await request.json();
    const {
      productId,
      planId,
      customerName,
      customerEmail,
      customerPhone,
      couponCode,
      quantity = 1,
      utmSource,
      utmMedium,
      utmCampaign,
      utmContent,
      utmTerm,
      fbp,
      fbc,
    } = body;

    // Validate customer inputs
    if (!customerEmail || !customerEmail.includes("@")) {
      return NextResponse.json(
        { error: "A valid email address is required for digital delivery." },
        { status: 400 }
      );
    }

    if (!productId || !planId) {
      return NextResponse.json(
        { error: "Product and plan selection are required." },
        { status: 400 }
      );
    }

    // Resolve plan and product strictly from database
    const plan = await prisma.plan.findFirst({
      where: { id: planId, productId, isActive: true },
      include: { product: true },
    });

    if (!plan) {
      return NextResponse.json(
        { error: "Selected product or plan is no longer available." },
        { status: 404 }
      );
    }

    // Enforce stock check if finite
    if (plan.stockPolicy === "FINITE" && plan.availableQuantity <= 0) {
      // Check if credentials available in inventory
      const inStockCount = await prisma.credentialInventory.count({
        where: { planId: plan.id, status: "AVAILABLE" },
      });
      if (inStockCount <= 0) {
        return NextResponse.json(
          { error: "This plan is currently out of stock. Please check back soon or contact support." },
          { status: 409 }
        );
      }
    }

    const qty = Math.max(1, Math.min(10, parseInt(quantity, 10) || 1));
    const subtotal = plan.price * qty;

    // Revalidate coupon server-side
    let discountAmount = 0;
    let couponId = null;

    if (couponCode && typeof couponCode === "string") {
      const cleanCode = couponCode.trim().toUpperCase();
      const coupon = await prisma.coupon.findUnique({
        where: { code: cleanCode, isActive: true },
      });

      if (coupon) {
        const notExpired = (!coupon.startDate || new Date(coupon.startDate) <= new Date()) &&
                           (!coupon.endDate || new Date(coupon.endDate) >= new Date());
        const hasUsage = !coupon.usageLimit || coupon.usedCount < coupon.usageLimit;
        const meetsMin = !coupon.minOrderAmount || subtotal >= coupon.minOrderAmount;

        if (notExpired && hasUsage && meetsMin) {
          couponId = coupon.id;
          if (coupon.discountType === "PERCENTAGE") {
            discountAmount = Math.round((subtotal * coupon.discountValue) / 100);
            if (coupon.maxDiscountAmount && discountAmount > coupon.maxDiscountAmount) {
              discountAmount = coupon.maxDiscountAmount;
            }
          } else {
            discountAmount = Math.min(subtotal, coupon.discountValue);
          }
        }
      }
    }

    const totalAmount = Math.max(100, subtotal - discountAmount); // minimum 100 paise (₹1) for Razorpay

    // Generate cryptographic identifiers
    const publicId = `ord_${generateSecureToken(16)}`;
    const orderNumber = generateOrderNumber();
    const rawAccessToken = generateSecureToken(24);
    const accessTokenHash = sha256Hash(rawAccessToken);

    // Create immutable plan snapshot
    const planSnapshot = JSON.stringify({
      productTitle: plan.product.title,
      productSlug: plan.product.slug,
      planName: plan.name,
      planSku: plan.sku,
      duration: plan.duration,
      unitPrice: plan.price,
      currency: plan.currency,
      benefits: plan.benefits,
      restrictions: plan.restrictions,
      deviceLimit: plan.deviceLimit,
      deliveryMethod: plan.deliveryMethod,
      deliveryInstructions: plan.deliveryInstructions,
      bonusEnabled: plan.product.bonusEnabled,
      bonusTitle: plan.product.bonusTitle,
      purchasedAt: new Date().toISOString(),
    });

    // Create Razorpay order on server
    const razorpayOrder = await createRazorpayOrder({
      amountPaise: totalAmount,
      currency: plan.currency || "INR",
      receipt: orderNumber,
      notes: {
        order_number: orderNumber,
        customer_email: customerEmail.trim().toLowerCase(),
        product_slug: plan.product.slug,
      },
    });

    // Create Order in DB
    const order = await prisma.order.create({
      data: {
        publicId,
        orderNumber,
        accessTokenHash,
        customerName: customerName ? customerName.trim() : "Customer",
        customerEmail: customerEmail.trim().toLowerCase(),
        customerPhone: customerPhone ? customerPhone.trim() : null,
        productId: plan.product.id,
        planId: plan.id,
        quantity: qty,
        planSnapshot,
        unitPrice: plan.price,
        discountAmount,
        totalAmount,
        currency: plan.currency || "INR",
        paymentStatus: "PENDING",
        fulfillmentStatus: "PENDING",
        bonusStatus: plan.product.bonusEnabled ? "PENDING_INPUT" : "NOT_APPLICABLE",
        razorpayOrderId: razorpayOrder.id,
        couponId,
        couponCode: couponCode ? String(couponCode).toUpperCase() : null,
        utmSource: utmSource ? String(utmSource) : null,
        utmMedium: utmMedium ? String(utmMedium) : null,
        utmCampaign: utmCampaign ? String(utmCampaign) : null,
        utmContent: utmContent ? String(utmContent) : null,
        utmTerm: utmTerm ? String(utmTerm) : null,
        fbp: fbp ? String(fbp) : null,
        fbc: fbc ? String(fbc) : null,
      },
    });

    // Send server-side Meta Conversions API InitiateCheckout event
    const eventId = `initiate_${order.publicId}`;
    sendMetaServerEvent({
      eventName: "InitiateCheckout",
      eventId,
      sourceUrl: `${process.env.APP_URL || "http://localhost:3000"}/checkout`,
      userData: {
        email: customerEmail,
        phone: customerPhone,
        fbp,
        fbc,
      },
      customData: {
        currency: plan.currency || "INR",
        value: totalAmount / 100,
        content_name: `${plan.product.title} - ${plan.name}`,
        content_ids: [plan.sku],
        content_type: "product",
        order_id: order.orderNumber,
      },
      productOverridePixelId: plan.product.pixelIdOverride,
      productOverrideAccessToken: plan.product.metaAccessTokenOverride,
    }).catch((e) => console.error("Meta CAPI InitiateCheckout error:", e));

    return NextResponse.json({
      success: true,
      order: {
        publicId: order.publicId,
        orderNumber: order.orderNumber,
        totalAmount: order.totalAmount,
        currency: order.currency,
      },
      razorpay: {
        orderId: razorpayOrder.id,
        amount: razorpayOrder.amount,
        currency: razorpayOrder.currency,
        keyId: razorpayOrder.key_id,
        isMock: razorpayOrder.isMock,
      },
      accessToken: rawAccessToken,
    });
  } catch (error) {
    console.error("Initiate checkout error:", error);
    return NextResponse.json(
      { error: "Failed to initiate payment. Please try again." },
      { status: 500 }
    );
  }
}

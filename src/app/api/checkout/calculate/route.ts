import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";

export async function POST(request: Request) {
  try {
    const body = await request.json();
    const { productId, planId, couponCode, quantity = 1 } = body;

    if (!productId || !planId) {
      return NextResponse.json(
        { error: "Product and Plan are required." },
        { status: 400 }
      );
    }

    const plan = await prisma.plan.findFirst({
      where: { id: planId, productId, isActive: true },
      include: { product: true },
    });

    if (!plan) {
      return NextResponse.json(
        { error: "Selected plan is unavailable or inactive." },
        { status: 404 }
      );
    }

    const qty = Math.max(1, Math.min(10, parseInt(quantity, 10) || 1));
    const unitPrice = plan.price; // in paise
    const subtotal = unitPrice * qty;

    let discountAmount = 0;
    let appliedCoupon = null;
    let couponMessage = null;

    if (couponCode && typeof couponCode === "string") {
      const cleanCode = couponCode.trim().toUpperCase();
      const coupon = await prisma.coupon.findUnique({
        where: { code: cleanCode },
      });

      if (!coupon || !coupon.isActive) {
        couponMessage = "Invalid or expired coupon code.";
      } else if (coupon.startDate && new Date(coupon.startDate) > new Date()) {
        couponMessage = "Coupon offer has not started yet.";
      } else if (coupon.endDate && new Date(coupon.endDate) < new Date()) {
        couponMessage = "Coupon code has expired.";
      } else if (coupon.usageLimit && coupon.usedCount >= coupon.usageLimit) {
        couponMessage = "Coupon usage limit has been reached.";
      } else if (coupon.minOrderAmount && subtotal < coupon.minOrderAmount) {
        couponMessage = `Minimum order amount of ₹${(coupon.minOrderAmount / 100).toFixed(0)} required.`;
      } else {
        // Calculate discount
        if (coupon.discountType === "PERCENTAGE") {
          discountAmount = Math.round((subtotal * coupon.discountValue) / 100);
          if (coupon.maxDiscountAmount && discountAmount > coupon.maxDiscountAmount) {
            discountAmount = coupon.maxDiscountAmount;
          }
        } else {
          // FIXED discount in paise
          discountAmount = Math.min(subtotal, coupon.discountValue);
        }

        appliedCoupon = {
          id: coupon.id,
          code: coupon.code,
          discountAmount,
        };
        couponMessage = `Coupon applied! You save ₹${(discountAmount / 100).toFixed(0)}`;
      }
    }

    const totalAmount = Math.max(0, subtotal - discountAmount);

    return NextResponse.json({
      success: true,
      unitPrice,
      quantity: qty,
      subtotal,
      discountAmount,
      totalAmount,
      currency: plan.currency || "INR",
      coupon: appliedCoupon,
      couponMessage,
      plan: {
        id: plan.id,
        name: plan.name,
        duration: plan.duration,
        sku: plan.sku,
        deliveryMethod: plan.deliveryMethod,
      },
      product: {
        id: plan.product.id,
        title: plan.product.title,
        slug: plan.product.slug,
      },
    });
  } catch (error) {
    console.error("Calculate checkout error:", error);
    return NextResponse.json(
      { error: "Failed to calculate order totals." },
      { status: 500 }
    );
  }
}

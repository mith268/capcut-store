import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { getRazorpayConfig, verifyPaymentSignature } from "@/lib/razorpay";
import { fulfillOrder } from "@/lib/fulfillment";
import { sendMetaServerEvent } from "@/lib/meta-tracking";

export async function POST(request: Request) {
  try {
    const body = await request.json();
    const {
      publicId,
      razorpay_order_id,
      razorpay_payment_id,
      razorpay_signature,
      accessToken,
    } = body;

    if (!publicId || !razorpay_order_id || !razorpay_payment_id) {
      return NextResponse.json(
        { error: "Missing required payment verification parameters." },
        { status: 400 }
      );
    }

    const order = await prisma.order.findUnique({
      where: { publicId },
      include: { plan: true, product: true },
    });

    if (!order) {
      return NextResponse.json({ error: "Order not found." }, { status: 404 });
    }

    // If order was already verified and paid, return success immediately (idempotency)
    if (order.paymentStatus === "PAID") {
      return NextResponse.json({
        success: true,
        alreadyProcessed: true,
        redirectUrl: `/orders/${order.publicId}?token=${accessToken || ""}`,
      });
    }

    const { keySecret } = await getRazorpayConfig();

    // Verify HMAC SHA-256 signature
    const isValid = verifyPaymentSignature({
      orderId: razorpay_order_id,
      paymentId: razorpay_payment_id,
      signature: razorpay_signature || "",
      secret: keySecret,
    });

    if (!isValid) {
      await prisma.order.update({
        where: { id: order.id },
        data: {
          paymentStatus: "FAILED",
          notes: "Payment signature mismatch / forgery attempt rejected.",
        },
      });

      return NextResponse.json(
        { error: "Payment verification failed: invalid signature." },
        { status: 400 }
      );
    }

    // Mark order as PAID
    await prisma.order.update({
      where: { id: order.id },
      data: {
        paymentStatus: "PAID",
        razorpayPaymentId: razorpay_payment_id,
        razorpaySignature: razorpay_signature,
        paidAt: new Date(),
        updatedAt: new Date(),
      },
    });

    // If a coupon was used, increment used count
    if (order.couponId) {
      await prisma.coupon.update({
        where: { id: order.couponId },
        data: { usedCount: { increment: 1 } },
      }).catch((e) => console.error("Error updating coupon count:", e));
    }

    // Atomically fulfill order with credentials or digital delivery
    const fulfillmentResult = await fulfillOrder(order.id);

    // Send Meta Conversions API Purchase Event
    const eventId = `purchase_${order.publicId}`;
    sendMetaServerEvent({
      eventName: "Purchase",
      eventId,
      sourceUrl: `${process.env.APP_URL || "http://localhost:3000"}/checkout`,
      userData: {
        email: order.customerEmail,
        phone: order.customerPhone || undefined,
        fbp: order.fbp || undefined,
        fbc: order.fbc || undefined,
      },
      customData: {
        currency: order.currency,
        value: order.totalAmount / 100, // standard currency units
        content_name: `${order.product.title} - ${order.plan.name}`,
        content_ids: [order.plan.sku],
        content_type: "product",
        order_id: order.orderNumber,
      },
      productOverridePixelId: order.product.pixelIdOverride,
      productOverrideAccessToken: order.product.metaAccessTokenOverride,
    }).catch((e) => console.error("Meta CAPI Purchase error:", e));

    return NextResponse.json({
      success: true,
      message: fulfillmentResult.message,
      fulfillmentStatus: fulfillmentResult.status,
      redirectUrl: `/orders/${order.publicId}?token=${accessToken || ""}`,
    });
  } catch (error) {
    console.error("Payment verification route error:", error);
    return NextResponse.json(
      { error: "Internal error processing payment verification." },
      { status: 500 }
    );
  }
}

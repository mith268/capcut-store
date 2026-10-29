import { NextResponse } from "next/server";
import { headers } from "next/headers";
import { prisma } from "@/lib/prisma";
import { getRazorpayConfig, verifyWebhookSignature } from "@/lib/razorpay";
import { fulfillOrder } from "@/lib/fulfillment";

export async function POST(request: Request) {
  try {
    const rawBody = await request.text();
    const headersList = headers();
    const signature = headersList.get("x-razorpay-signature");

    if (!signature) {
      return NextResponse.json(
        { error: "Missing webhook signature header." },
        { status: 400 }
      );
    }

    const { webhookSecret } = await getRazorpayConfig();

    const isValid = verifyWebhookSignature(rawBody, signature, webhookSecret);
    if (!isValid) {
      return NextResponse.json(
        { error: "Invalid webhook signature." },
        { status: 400 }
      );
    }

    const payload = JSON.parse(rawBody);
    const event = payload.event;

    // Handle payment.captured or order.paid
    if (event === "payment.captured" || event === "order.paid") {
      const paymentEntity = payload.payload?.payment?.entity;
      const orderId = paymentEntity?.order_id || payload.payload?.order?.entity?.id;

      if (!orderId) {
        return NextResponse.json({ message: "No order reference in payload" }, { status: 200 });
      }

      const order = await prisma.order.findFirst({
        where: { razorpayOrderId: orderId },
        include: { plan: true },
      });

      if (!order) {
        return NextResponse.json({ message: "Order not found in database" }, { status: 200 });
      }

      // Idempotency: if already paid, return 200 immediately
      if (order.paymentStatus === "PAID") {
        return NextResponse.json({ message: "Order already paid and processed" }, { status: 200 });
      }

      // Mark order as PAID
      await prisma.order.update({
        where: { id: order.id },
        data: {
          paymentStatus: "PAID",
          razorpayPaymentId: paymentEntity?.id || order.razorpayPaymentId,
          paidAt: new Date(),
          updatedAt: new Date(),
        },
      });

      // Atomically fulfill order
      await fulfillOrder(order.id);

      return NextResponse.json({ success: true, message: "Order processed via webhook" });
    }

    return NextResponse.json({ received: true });
  } catch (error) {
    console.error("Razorpay webhook error:", error);
    return NextResponse.json(
      { error: "Webhook processing failed" },
      { status: 500 }
    );
  }
}

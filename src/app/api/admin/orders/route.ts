import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { getAuthenticatedAdmin } from "@/lib/auth";

export const dynamic = "force-dynamic";

export async function GET(request: Request) {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const { searchParams } = new URL(request.url);
    const query = searchParams.get("q") || "";
    const paymentStatus = searchParams.get("paymentStatus") || "";
    const fulfillmentStatus = searchParams.get("fulfillmentStatus") || "";

    const whereClause: any = {};

    if (query) {
      whereClause.OR = [
        { orderNumber: { contains: query } },
        { customerName: { contains: query } },
        { customerEmail: { contains: query } },
        { customerPhone: { contains: query } },
        { razorpayPaymentId: { contains: query } },
      ];
    }

    if (paymentStatus) {
      whereClause.paymentStatus = paymentStatus;
    }

    if (fulfillmentStatus) {
      whereClause.fulfillmentStatus = fulfillmentStatus;
    }

    const orders = await prisma.order.findMany({
      where: whereClause,
      include: {
        product: { select: { title: true, slug: true } },
        plan: { select: { name: true, duration: true, deliveryMethod: true } },
      },
      orderBy: { createdAt: "desc" },
      take: 100,
    });

    return NextResponse.json({ orders });
  } catch (error) {
    console.error("Admin orders fetch error:", error);
    return NextResponse.json({ error: "Failed to fetch orders" }, { status: 500 });
  }
}

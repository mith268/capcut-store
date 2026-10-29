import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { getAuthenticatedAdmin } from "@/lib/auth";
import { revealCredential } from "@/lib/fulfillment";

export async function GET(
  request: Request,
  { params }: { params: { id: string } }
) {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const order = await prisma.order.findUnique({
      where: { id: params.id },
      include: {
        product: true,
        plan: true,
        fulfillments: {
          orderBy: { createdAt: "desc" },
        },
      },
    });

    if (!order) {
      return NextResponse.json({ error: "Order not found" }, { status: 404 });
    }

    // Decrypt fulfillment history credentials for staff inspection
    const decryptedFulfillments = order.fulfillments.map((f) => {
      let content = null;
      if (f.encryptedDeliveredContent) {
        try {
          content = revealCredential({
            encryptedData: f.encryptedDeliveredContent,
            iv: f.iv || "",
            authTag: f.authTag || "",
          });
        } catch {
          content = { notes: "Error decrypting content" };
        }
      }
      return {
        id: f.id,
        createdAt: f.createdAt,
        deliveryMethod: f.deliveryMethod,
        replacedReason: f.replacedReason,
        replacedAt: f.replacedAt,
        credentials: content,
      };
    });

    return NextResponse.json({
      order: {
        ...order,
        fulfillments: decryptedFulfillments,
      },
    });
  } catch (error) {
    console.error("Admin get order details error:", error);
    return NextResponse.json({ error: "Failed to fetch order details" }, { status: 500 });
  }
}

export async function PATCH(
  request: Request,
  { params }: { params: { id: string } }
) {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const body = await request.json();
    const { fulfillmentStatus, paymentStatus, bonusStatus, notes } = body;

    const updated = await prisma.order.update({
      where: { id: params.id },
      data: {
        fulfillmentStatus: fulfillmentStatus || undefined,
        paymentStatus: paymentStatus || undefined,
        bonusStatus: bonusStatus || undefined,
        notes: notes !== undefined ? notes : undefined,
        updatedAt: new Date(),
      },
    });

    await prisma.auditLog.create({
      data: {
        userId: admin.id,
        action: "UPDATE_ORDER",
        entityType: "ORDER",
        entityId: updated.id,
        details: JSON.stringify({ fulfillmentStatus, paymentStatus, bonusStatus }),
      },
    });

    return NextResponse.json({ success: true, order: updated });
  } catch (error) {
    console.error("Admin update order error:", error);
    return NextResponse.json({ error: "Failed to update order" }, { status: 500 });
  }
}

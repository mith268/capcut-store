import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { sha256Hash } from "@/lib/crypto";

export async function POST(
  request: Request,
  { params }: { params: { publicId: string } }
) {
  try {
    const { publicId } = params;
    const body = await request.json();
    const { token, email, canvaEmail } = body;

    if (!publicId || !canvaEmail || !canvaEmail.includes("@")) {
      return NextResponse.json(
        { error: "A valid Canva email address is required to claim the bonus." },
        { status: 400 }
      );
    }

    const order = await prisma.order.findUnique({
      where: { publicId },
    });

    if (!order) {
      return NextResponse.json({ error: "Order not found." }, { status: 404 });
    }

    // Verify access either via token or matching customer email
    const tokenValid = token && sha256Hash(token) === order.accessTokenHash;
    const emailValid = email && email.trim().toLowerCase() === order.customerEmail.toLowerCase();

    if (!tokenValid && !emailValid) {
      return NextResponse.json(
        { error: "Unauthorized access to order." },
        { status: 403 }
      );
    }

    // Update order with Canva email and update bonus status
    const updated = await prisma.order.update({
      where: { id: order.id },
      data: {
        bonusCustomerInput: canvaEmail.trim().toLowerCase(),
        bonusStatus: "PROCESSING",
        updatedAt: new Date(),
      },
    });

    return NextResponse.json({
      success: true,
      message: "Bonus claim submitted successfully! We will grant access to your Canva email.",
      bonusCustomerInput: updated.bonusCustomerInput,
      bonusStatus: updated.bonusStatus,
    });
  } catch (error) {
    console.error("Error claiming bonus:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}

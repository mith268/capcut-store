import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";

export async function POST(request: Request) {
  try {
    const body = await request.json();
    const { orderIdentifier, contact } = body;

    if (!orderIdentifier || !contact) {
      return NextResponse.json(
        { error: "Please provide both Order Number and your Email or Phone." },
        { status: 400 }
      );
    }

    const cleanOrder = String(orderIdentifier).trim().toUpperCase();
    const cleanContact = String(contact).trim().toLowerCase();

    // Lookup by orderNumber OR publicId
    const order = await prisma.order.findFirst({
      where: {
        OR: [
          { orderNumber: cleanOrder },
          { publicId: orderIdentifier.trim() },
        ],
      },
      select: {
        id: true,
        publicId: true,
        orderNumber: true,
        customerEmail: true,
        customerPhone: true,
      },
    });

    if (!order) {
      return NextResponse.json(
        { error: "No order found matching the provided order number." },
        { status: 404 }
      );
    }

    // Verify contact matches email or phone
    const emailMatch = order.customerEmail.toLowerCase() === cleanContact;
    const phoneMatch = order.customerPhone && order.customerPhone.replace(/\D/g, "").includes(cleanContact.replace(/\D/g, ""));

    if (!emailMatch && !phoneMatch) {
      return NextResponse.json(
        { error: "The provided email or phone does not match this order." },
        { status: 403 }
      );
    }

    // Match verified! Return the publicId and an authorized access indicator
    return NextResponse.json({
      success: true,
      publicId: order.publicId,
      orderNumber: order.orderNumber,
      verifiedEmail: order.customerEmail,
    });
  } catch (error) {
    console.error("Order lookup error:", error);
    return NextResponse.json(
      { error: "Failed to look up order. Please try again." },
      { status: 500 }
    );
  }
}

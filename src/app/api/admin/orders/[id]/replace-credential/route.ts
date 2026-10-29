import { NextResponse } from "next/server";
import { getAuthenticatedAdmin } from "@/lib/auth";
import { replaceOrderCredential } from "@/lib/fulfillment";

export async function POST(
  request: Request,
  { params }: { params: { id: string } }
) {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const body = await request.json();
    const { reason } = body;

    if (!reason || reason.trim().length < 3) {
      return NextResponse.json(
        { error: "A valid explanation / replacement reason is required for audit logs." },
        { status: 400 }
      );
    }

    await replaceOrderCredential({
      orderId: params.id,
      reason: reason.trim(),
      staffUserId: admin.id,
    });

    return NextResponse.json({
      success: true,
      message: "Order credentials replaced successfully. Audit log recorded.",
    });
  } catch (error: any) {
    console.error("Replace credential error:", error);
    return NextResponse.json(
      { error: error.message || "Failed to replace order credential." },
      { status: 400 }
    );
  }
}

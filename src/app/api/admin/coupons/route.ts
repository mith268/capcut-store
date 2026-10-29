import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { getAuthenticatedAdmin } from "@/lib/auth";

export const dynamic = "force-dynamic";

export async function GET() {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const coupons = await prisma.coupon.findMany({
      orderBy: { createdAt: "desc" },
    });

    return NextResponse.json({ coupons });
  } catch (error) {
    console.error("Admin coupons fetch error:", error);
    return NextResponse.json({ error: "Failed to fetch coupons" }, { status: 500 });
  }
}

export async function POST(request: Request) {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const body = await request.json();
    const {
      code,
      discountType = "PERCENTAGE",
      discountValue,
      minOrderAmountINR = 0,
      usageLimit,
    } = body;

    if (!code || !discountValue) {
      return NextResponse.json(
        { error: "Coupon code and discount value are required." },
        { status: 400 }
      );
    }

    const cleanCode = String(code).trim().toUpperCase();

    const existing = await prisma.coupon.findUnique({
      where: { code: cleanCode },
    });

    if (existing) {
      return NextResponse.json(
        { error: "A coupon with this code already exists." },
        { status: 409 }
      );
    }

    // If FIXED discount, convert INR to paise
    const valueNum =
      discountType === "FIXED"
        ? Math.round(Number(discountValue) * 100)
        : Number(discountValue);

    const coupon = await prisma.coupon.create({
      data: {
        code: cleanCode,
        discountType,
        discountValue: valueNum,
        minOrderAmount: Math.round(Number(minOrderAmountINR) * 100),
        usageLimit: usageLimit ? Number(usageLimit) : null,
        isActive: true,
      },
    });

    await prisma.auditLog.create({
      data: {
        userId: admin.id,
        action: "CREATE_COUPON",
        entityType: "COUPON",
        entityId: coupon.id,
        details: JSON.stringify({ code: coupon.code, discountType, discountValue: valueNum }),
      },
    });

    return NextResponse.json({ success: true, coupon });
  } catch (error) {
    console.error("Admin create coupon error:", error);
    return NextResponse.json({ error: "Failed to create coupon" }, { status: 500 });
  }
}

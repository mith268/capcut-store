import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";

export const dynamic = "force-dynamic";

export async function GET() {
  try {
    // Find first featured or published active product
    const product = await prisma.product.findFirst({
      where: {
        status: "PUBLISHED",
      },
      include: {
        plans: {
          where: { isActive: true },
          orderBy: { displayOrder: "asc" },
        },
      },
      orderBy: [
        { isFeatured: "desc" },
        { createdAt: "desc" },
      ],
    });

    if (!product || product.plans.length === 0) {
      return NextResponse.json(
        { error: "No published product or plan found" },
        { status: 404 }
      );
    }

    const defaultPlan = product.plans[0];

    return NextResponse.json({
      product: {
        id: product.id,
        title: product.title,
        slug: product.slug,
      },
      plan: {
        id: defaultPlan.id,
        name: defaultPlan.name,
        priceINR: defaultPlan.price / 100,
        duration: defaultPlan.duration,
      },
    });
  } catch (error) {
    console.error("Error fetching default product:", error);
    return NextResponse.json(
      { error: "Internal server error" },
      { status: 500 }
    );
  }
}

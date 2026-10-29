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

    const products = await prisma.product.findMany({
      where: { status: { not: "ARCHIVED" } },
      include: {
        category: true,
        plans: {
          where: { isActive: true },
          orderBy: { displayOrder: "asc" },
        },
        _count: {
          select: { orders: true, sections: true },
        },
      },
      orderBy: [{ isFeatured: "desc" }, { createdAt: "desc" }],
    });

    return NextResponse.json({ products });
  } catch (error) {
    console.error("Admin products fetch error:", error);
    return NextResponse.json({ error: "Failed to fetch products" }, { status: 500 });
  }
}

export async function POST(request: Request) {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const body = await request.json();
    const { title, slug, categoryId, priceINR = 299, duration = "1 Month" } = body;

    if (!title || !slug) {
      return NextResponse.json(
        { error: "Product title and unique slug are required." },
        { status: 400 }
      );
    }

    const cleanSlug = String(slug).trim().toLowerCase().replace(/[^a-z0-9-]/g, "-");

    // Check slug collision
    const existing = await prisma.product.findUnique({
      where: { slug: cleanSlug },
    });

    if (existing) {
      return NextResponse.json(
        { error: "A product with this URL slug already exists." },
        { status: 409 }
      );
    }

    // Create product with a starter plan
    const product = await prisma.product.create({
      data: {
        title: title.trim(),
        slug: cleanSlug,
        shortTitle: title.trim(),
        subtitle: "Official digital license with instant setup instructions and guaranteed replacement warranty.",
        shortDescription: "Official digital license with instant setup instructions and guaranteed replacement warranty.",
        categoryId: categoryId || null,
        status: "PUBLISHED",
        plans: {
          create: {
            name: `${duration} Access`,
            sku: `${cleanSlug.toUpperCase()}-${duration.toUpperCase().replace(/\s+/g, "")}`,
            duration: duration,
            price: Math.round(Number(priceINR) * 100), // in paise
            compareAtPrice: Math.round(Number(priceINR) * 100 * 1.5),
            deliveryMethod: "CREDENTIALS",
            stockPolicy: "UNLIMITED",
            isDefault: true,
            isRecommended: true,
          },
        },
      },
      include: { plans: true },
    });

    // Record audit log
    await prisma.auditLog.create({
      data: {
        userId: admin.id,
        action: "CREATE_PRODUCT",
        entityType: "PRODUCT",
        entityId: product.id,
        details: JSON.stringify({ title: product.title, slug: product.slug }),
      },
    });

    return NextResponse.json({ success: true, product });
  } catch (error) {
    console.error("Admin product creation error:", error);
    return NextResponse.json({ error: "Failed to create product" }, { status: 500 });
  }
}

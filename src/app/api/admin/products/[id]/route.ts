import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { getAuthenticatedAdmin } from "@/lib/auth";

export async function GET(
  request: Request,
  { params }: { params: { id: string } }
) {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const product = await prisma.product.findUnique({
      where: { id: params.id },
      include: {
        category: true,
        plans: {
          orderBy: { displayOrder: "asc" },
          include: {
            _count: {
              select: {
                inventories: true,
              },
            },
          },
        },
        sections: {
          orderBy: { displayOrder: "asc" },
        },
      },
    });

    if (!product) {
      return NextResponse.json({ error: "Product not found" }, { status: 404 });
    }

    return NextResponse.json({ product });
  } catch (error) {
    console.error("Admin get product error:", error);
    return NextResponse.json({ error: "Failed to fetch product" }, { status: 500 });
  }
}

export async function PUT(
  request: Request,
  { params }: { params: { id: string } }
) {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const body = await request.json();
    const {
      title,
      slug,
      tagline,
      heroTitle,
      heroSubtitle,
      heroBadge,
      videoUrl,
      videoPoster,
      categoryId,
      isPublished,
      isFeatured,
      bonusEnabled,
      bonusTitle,
      bonusBadge,
      bonusDescription,
      bonusSubtitle,
      bonusExplanation,
      stickyBarEnabled,
      stickyBarText,
      stickyBarButtonLabel,
      pixelIdOverride,
      metaAccessTokenOverride,
      plans,
      sections,
    } = body;

    const existing = await prisma.product.findUnique({
      where: { id: params.id },
    });

    if (!existing) {
      return NextResponse.json({ error: "Product not found" }, { status: 404 });
    }

    const cleanSlug = slug ? String(slug).trim().toLowerCase().replace(/[^a-z0-9-]/g, "-") : existing.slug;

    // If slug changed, create a redirect record
    if (cleanSlug !== existing.slug) {
      await prisma.productSlugRedirect.upsert({
        where: { oldSlug: existing.slug },
        update: { newSlug: cleanSlug, productId: existing.id },
        create: {
          oldSlug: existing.slug,
          newSlug: cleanSlug,
          productId: existing.id,
        },
      }).catch((e) => console.error("Slug redirect error:", e));
    }

    // Update Product Details
    const updated = await prisma.product.update({
      where: { id: params.id },
      data: {
        title: title !== undefined ? title : existing.title,
        slug: cleanSlug,
        shortTitle: title !== undefined ? title : existing.shortTitle,
        subtitle: heroSubtitle !== undefined ? heroSubtitle : existing.subtitle,
        shortDescription: heroSubtitle !== undefined ? heroSubtitle : existing.shortDescription,
        videoUrl: videoUrl !== undefined ? videoUrl : existing.videoUrl,
        categoryId: categoryId !== undefined ? categoryId : existing.categoryId,
        status: isPublished !== undefined ? (isPublished ? "PUBLISHED" : "DRAFT") : existing.status,
        isFeatured: isFeatured !== undefined ? Boolean(isFeatured) : existing.isFeatured,
        bonusEnabled: bonusEnabled !== undefined ? Boolean(bonusEnabled) : existing.bonusEnabled,
        bonusTitle: bonusTitle !== undefined ? bonusTitle : existing.bonusTitle,
        bonusBadge: bonusBadge !== undefined ? bonusBadge : existing.bonusBadge,
        bonusDescription: bonusDescription !== undefined ? bonusDescription : existing.bonusDescription,
        bonusSubtitle: bonusSubtitle !== undefined ? bonusSubtitle : existing.bonusSubtitle,
        bonusExplanation: bonusExplanation !== undefined ? bonusExplanation : existing.bonusExplanation,
        stickyBarEnabled: stickyBarEnabled !== undefined ? Boolean(stickyBarEnabled) : existing.stickyBarEnabled,
        stickyBarText: stickyBarText !== undefined ? stickyBarText : existing.stickyBarText,
        stickyBarButtonLabel: stickyBarButtonLabel !== undefined ? stickyBarButtonLabel : existing.stickyBarButtonLabel,
        pixelIdOverride: pixelIdOverride !== undefined ? pixelIdOverride : existing.pixelIdOverride,
        metaAccessTokenOverride: metaAccessTokenOverride !== undefined ? metaAccessTokenOverride : existing.metaAccessTokenOverride,
        updatedAt: new Date(),
      },
    });

    // Update plans if provided
    if (Array.isArray(plans)) {
      for (const plan of plans) {
        if (plan.id && !plan.id.startsWith("new_")) {
          await prisma.plan.update({
            where: { id: plan.id },
            data: {
              name: plan.name,
              sku: plan.sku,
              duration: plan.duration,
              price: Math.round(Number(plan.priceINR) * 100),
              compareAtPrice: plan.compareAtPriceINR ? Math.round(Number(plan.compareAtPriceINR) * 100) : null,
              deliveryMethod: plan.deliveryMethod || "CREDENTIALS",
              stockPolicy: plan.stockPolicy || "FINITE",
              availableQuantity: Number(plan.availableQuantity) || 0,
              isActive: plan.isActive !== undefined ? Boolean(plan.isActive) : true,
              isDefault: Boolean(plan.isDefault),
              isRecommended: Boolean(plan.isRecommended),
              displayOrder: Number(plan.displayOrder) || 0,
              benefits: typeof plan.benefits === "string" ? plan.benefits : JSON.stringify(plan.benefits || []),
            },
          }).catch((e) => console.error("Error updating plan:", e));
        } else if (plan.name && plan.priceINR) {
          await prisma.plan.create({
            data: {
              productId: updated.id,
              name: plan.name,
              sku: plan.sku || `${updated.slug.toUpperCase()}-${Date.now().toString().slice(-4)}`,
              duration: plan.duration || "1 Month",
              price: Math.round(Number(plan.priceINR) * 100),
              compareAtPrice: plan.compareAtPriceINR ? Math.round(Number(plan.compareAtPriceINR) * 100) : null,
              deliveryMethod: plan.deliveryMethod || "CREDENTIALS",
              stockPolicy: plan.stockPolicy || "FINITE",
              availableQuantity: Number(plan.availableQuantity) || 0,
              isActive: true,
              isDefault: Boolean(plan.isDefault),
              isRecommended: Boolean(plan.isRecommended),
              displayOrder: Number(plan.displayOrder) || 0,
              benefits: typeof plan.benefits === "string" ? plan.benefits : JSON.stringify(plan.benefits || []),
            },
          }).catch((e) => console.error("Error creating plan:", e));
        }
      }
    }

    // Record audit log
    await prisma.auditLog.create({
      data: {
        userId: admin.id,
        action: "UPDATE_PRODUCT",
        entityType: "PRODUCT",
        entityId: updated.id,
        details: JSON.stringify({ title: updated.title, slug: updated.slug }),
      },
    });

    return NextResponse.json({ success: true, product: updated });
  } catch (error) {
    console.error("Admin update product error:", error);
    return NextResponse.json({ error: "Failed to update product" }, { status: 500 });
  }
}

export async function DELETE(
  request: Request,
  { params }: { params: { id: string } }
) {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    await prisma.product.update({
      where: { id: params.id },
      data: { status: "ARCHIVED" },
    });

    await prisma.auditLog.create({
      data: {
        userId: admin.id,
        action: "ARCHIVE_PRODUCT",
        entityType: "PRODUCT",
        entityId: params.id,
      },
    });

    return NextResponse.json({ success: true, message: "Product archived successfully" });
  } catch (error) {
    console.error("Admin delete product error:", error);
    return NextResponse.json({ error: "Failed to archive product" }, { status: 500 });
  }
}

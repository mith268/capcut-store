import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { getAuthenticatedAdmin } from "@/lib/auth";
import { encryptCredential } from "@/lib/crypto";

export const dynamic = "force-dynamic";

export async function GET() {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const plans = await prisma.plan.findMany({
      where: { isActive: true },
      include: {
        product: { select: { id: true, title: true, slug: true } },
        _count: {
          select: {
            inventories: true,
          },
        },
      },
      orderBy: [{ product: { title: "asc" } }, { displayOrder: "asc" }],
    });

    // Get count breakdown per plan
    const inventoryStats = await Promise.all(
      plans.map(async (plan) => {
        const available = await prisma.credentialInventory.count({
          where: { planId: plan.id, status: "AVAILABLE" },
        });
        const assigned = await prisma.credentialInventory.count({
          where: { planId: plan.id, status: "ASSIGNED" },
        });
        const retired = await prisma.credentialInventory.count({
          where: { planId: plan.id, status: "RETIRED" },
        });

        return {
          planId: plan.id,
          planName: plan.name,
          productTitle: plan.product.title,
          sku: plan.sku,
          deliveryMethod: plan.deliveryMethod,
          available,
          assigned,
          retired,
          total: available + assigned + retired,
        };
      })
    );

    // Get recent 30 inventory items
    const recentItems = await prisma.credentialInventory.findMany({
      orderBy: { createdAt: "desc" },
      take: 30,
      include: {
        plan: {
          select: {
            name: true,
            product: { select: { title: true } },
          },
        },
      },
    });

    return NextResponse.json({
      stats: inventoryStats,
      recentItems: recentItems.map((item) => ({
        id: item.id,
        planName: item.plan.name,
        productTitle: item.plan.product.title,
        status: item.status,
        identifier: item.plainHint || "Encrypted Entry",
        credentialType: "CREDENTIALS",
        deliveredAt: item.deliveredAt,
        createdAt: item.createdAt,
      })),
    });
  } catch (error) {
    console.error("Admin get inventory error:", error);
    return NextResponse.json({ error: "Failed to fetch inventory stats" }, { status: 500 });
  }
}

export async function POST(request: Request) {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const body = await request.json();
    const { planId, rawCredentials, credentialType = "CREDENTIALS", setupInstructions } = body;

    if (!planId || !rawCredentials || typeof rawCredentials !== "string") {
      return NextResponse.json(
        { error: "Plan selection and raw credentials content are required." },
        { status: 400 }
      );
    }

    const plan = await prisma.plan.findUnique({
      where: { id: planId },
      include: { product: true },
    });

    if (!plan) {
      return NextResponse.json({ error: "Selected plan not found" }, { status: 404 });
    }

    // Split lines
    const lines = rawCredentials
      .split("\n")
      .map((l) => l.trim())
      .filter((l) => l.length > 0);

    if (lines.length === 0) {
      return NextResponse.json({ error: "No valid lines provided to import." }, { status: 400 });
    }

    let importedCount = 0;

    for (const line of lines) {
      let payloadToEncrypt: any = {};
      let maskIdentifier = "Encrypted Item";

      if (credentialType === "CREDENTIALS") {
        // Support email:pass, email|pass, email pass
        const parts = line.split(/[:|,\s]+/);
        if (parts.length >= 2) {
          const user = parts[0];
          const pass = parts.slice(1).join(":");
          payloadToEncrypt = { emailOrUsername: user, password: pass };
          maskIdentifier = `${user.slice(0, 3)}***@${user.split("@")[1] || "account"}`;
        } else {
          payloadToEncrypt = { notes: line };
          maskIdentifier = `${line.slice(0, 8)}...`;
        }
      } else if (credentialType === "ACTIVATION_CODE") {
        payloadToEncrypt = { activationCode: line };
        maskIdentifier = `${line.slice(0, 4)}-****-${line.slice(-3)}`;
      } else if (credentialType === "LINK") {
        payloadToEncrypt = { activationLink: line };
        maskIdentifier = "Direct Activation Link";
      } else {
        payloadToEncrypt = { notes: line };
        maskIdentifier = "Digital Content";
      }

      const { encryptedData, iv, authTag } = encryptCredential(JSON.stringify(payloadToEncrypt));

      await prisma.credentialInventory.create({
        data: {
          planId: plan.id,
          status: "AVAILABLE",
          plainHint: maskIdentifier,
          encryptedData,
          iv,
          authTag,
          setupInstructions: setupInstructions ? String(setupInstructions) : null,
        },
      });

      importedCount++;
    }

    // If plan stockPolicy is FINITE, increment availableQuantity
    if (plan.stockPolicy === "FINITE") {
      await prisma.plan.update({
        where: { id: plan.id },
        data: { availableQuantity: { increment: importedCount } },
      });
    }

    // Record audit log
    await prisma.auditLog.create({
      data: {
        userId: admin.id,
        action: "IMPORT_INVENTORY",
        entityType: "INVENTORY",
        entityId: plan.id,
        details: JSON.stringify({
          planName: plan.name,
          productTitle: plan.product.title,
          importedCount,
          credentialType,
        }),
      },
    });

    return NextResponse.json({
      success: true,
      message: `Successfully imported and encrypted ${importedCount} credentials into the vault!`,
      importedCount,
    });
  } catch (error) {
    console.error("Admin import inventory error:", error);
    return NextResponse.json({ error: "Failed to import credentials." }, { status: 500 });
  }
}

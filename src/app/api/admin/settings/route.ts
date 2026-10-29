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

    const settings = await prisma.storeSetting.findMany({
      orderBy: { group: "asc" },
    });

    const settingsObj: Record<string, string> = {};
    for (const s of settings) {
      settingsObj[s.key] = s.value;
    }

    return NextResponse.json({ settings: settingsObj });
  } catch (error) {
    console.error("Admin settings fetch error:", error);
    return NextResponse.json({ error: "Failed to fetch settings" }, { status: 500 });
  }
}

export async function PUT(request: Request) {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const body = await request.json();
    const { settings } = body;

    if (!settings || typeof settings !== "object") {
      return NextResponse.json({ error: "Invalid settings payload" }, { status: 400 });
    }

    const updatedKeys: string[] = [];

    for (const [key, rawValue] of Object.entries(settings)) {
      if (typeof rawValue !== "string" && typeof rawValue !== "boolean") continue;
      const strVal = String(rawValue);

      // Determine group
      let group = "STORE";
      if (key.startsWith("razorpay_")) group = "PAYMENTS";
      else if (key.startsWith("meta_")) group = "TRACKING";
      else if (key.startsWith("whatsapp_") || key.startsWith("support_")) group = "NOTIFICATIONS";

      await prisma.storeSetting.upsert({
        where: { key },
        update: { value: strVal, group, updatedAt: new Date() },
        create: { key, value: strVal, group },
      });

      updatedKeys.push(key);
    }

    // Record audit log
    await prisma.auditLog.create({
      data: {
        userId: admin.id,
        action: "UPDATE_SETTINGS",
        entityType: "SETTINGS",
        details: JSON.stringify({ updatedKeys }),
      },
    });

    return NextResponse.json({
      success: true,
      message: "Store settings saved successfully!",
      updatedKeys,
    });
  } catch (error) {
    console.error("Admin update settings error:", error);
    return NextResponse.json({ error: "Failed to update settings" }, { status: 500 });
  }
}

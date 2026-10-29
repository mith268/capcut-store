import { NextResponse } from "next/server";
import { getAuthenticatedAdmin } from "@/lib/auth";
import { sendMetaServerEvent, getMetaConfig } from "@/lib/meta-tracking";

export async function POST(request: Request) {
  try {
    const admin = await getAuthenticatedAdmin();
    if (!admin) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const config = await getMetaConfig();

    if (!config.pixelId) {
      return NextResponse.json(
        { error: "Meta Pixel ID is not configured. Please add it in Store Settings first." },
        { status: 400 }
      );
    }

    if (!config.accessToken) {
      return NextResponse.json(
        { error: "Meta Conversions API Access Token is not configured. Please add it in Store Settings first." },
        { status: 400 }
      );
    }

    const testEventId = `test_${Date.now()}`;

    // Send a test Purchase event to Meta Graph API
    const result = await sendMetaServerEvent({
      eventName: "Purchase",
      eventId: testEventId,
      sourceUrl: `${process.env.APP_URL || "http://localhost:3000"}/admin/tracking`,
      userData: {
        email: "test.diagnostics@convora.local",
        phone: "+919999999999",
      },
      customData: {
        currency: "INR",
        value: 299,
        content_name: "Test Diagnostics Verification Order",
        order_id: "TEST-ORD-001",
      },
    });

    return NextResponse.json({
      success: result.success,
      message: result.success
        ? "Event successfully delivered to Meta CAPI Graph API"
        : (result.error || result.reason || "Event skipped or failed"),
      metaResponse: result.result || null,
      eventId: testEventId,
      pixelId: config.pixelId,
      hasTestEventCode: Boolean(config.testEventCode),
    });
  } catch (error: any) {
    console.error("Meta CAPI test event error:", error);
    return NextResponse.json(
      { error: error.message || "Failed to dispatch test event to Meta." },
      { status: 500 }
    );
  }
}

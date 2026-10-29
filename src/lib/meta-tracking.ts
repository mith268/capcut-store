import { hashForMeta } from "./crypto";
import { prisma } from "./prisma";

interface MetaEventParams {
  eventName: "PageView" | "ViewContent" | "AddToCart" | "InitiateCheckout" | "Purchase";
  eventId: string; // Critical for deduplication between Pixel and CAPI
  sourceUrl?: string;
  userData?: {
    email?: string;
    phone?: string;
    clientIp?: string;
    clientUserAgent?: string;
    fbp?: string;
    fbc?: string;
  };
  customData?: {
    currency?: string;
    value?: number; // In standard currency units, e.g. 299 for ₹299
    content_name?: string;
    content_category?: string;
    content_ids?: string[];
    content_type?: string;
    order_id?: string;
  };
  productOverridePixelId?: string | null;
  productOverrideAccessToken?: string | null;
}

/**
 * Fetch active Meta configuration from store settings or environment
 */
export async function getMetaConfig() {
  const settings = await prisma.storeSetting.findMany({
    where: {
      key: {
        in: ["meta_pixel_id", "meta_access_token", "meta_test_event_code", "meta_tracking_enabled"],
      },
    },
  });

  const settingsMap = new Map(settings.map((s) => [s.key, s.value]));

  const pixelId = settingsMap.get("meta_pixel_id") || process.env.META_PIXEL_ID || "";
  const accessToken = settingsMap.get("meta_access_token") || process.env.META_ACCESS_TOKEN || "";
  const testEventCode = settingsMap.get("meta_test_event_code") || process.env.META_TEST_EVENT_CODE || "";
  const isEnabled = (settingsMap.get("meta_tracking_enabled") ?? "true") === "true";

  return { pixelId, accessToken, testEventCode, isEnabled };
}

/**
 * Send a server-side Conversions API (CAPI) event to Meta graph API
 */
export async function sendMetaServerEvent(params: MetaEventParams) {
  const config = await getMetaConfig();

  const activePixelId = params.productOverridePixelId || config.pixelId;
  const activeAccessToken = params.productOverrideAccessToken || config.accessToken;

  // If tracking is disabled or no access token configured, record as skipped
  if (!config.isEnabled || !activePixelId || !activeAccessToken) {
    return {
      success: false,
      skipped: true,
      reason: "Meta Pixel ID or Access Token not configured or tracking disabled",
    };
  }

  // Normalize and hash user data per Meta specifications
  const userDataPayload: Record<string, unknown> = {};
  if (params.userData?.email) {
    userDataPayload.em = [hashForMeta(params.userData.email)];
  }
  if (params.userData?.phone) {
    // Strip non-digit characters
    const cleanPhone = params.userData.phone.replace(/[^0-9]/g, "");
    if (cleanPhone) {
      userDataPayload.ph = [hashForMeta(cleanPhone)];
    }
  }
  if (params.userData?.clientIp) {
    userDataPayload.client_ip_address = params.userData.clientIp;
  }
  if (params.userData?.clientUserAgent) {
    userDataPayload.client_user_agent = params.userData.clientUserAgent;
  }
  if (params.userData?.fbp) {
    userDataPayload.fbp = params.userData.fbp;
  }
  if (params.userData?.fbc) {
    userDataPayload.fbc = params.userData.fbc;
  }

  const eventPayload: Record<string, unknown> = {
    event_name: params.eventName,
    event_time: Math.floor(Date.now() / 1000),
    event_id: params.eventId,
    event_source_url: params.sourceUrl,
    action_source: "website",
    user_data: userDataPayload,
  };

  if (params.customData) {
    eventPayload.custom_data = params.customData;
  }

  const requestBody: Record<string, unknown> = {
    data: [eventPayload],
  };

  if (config.testEventCode) {
    requestBody.test_event_code = config.testEventCode;
  }

  try {
    const response = await fetch(
      `https://graph.facebook.com/v21.0/${activePixelId}/events?access_token=${activeAccessToken}`,
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(requestBody),
      }
    );

    const result = await response.json();

    // Persist in TrackingEvent table for diagnostics
    await prisma.trackingEvent.upsert({
      where: { eventId: params.eventId },
      update: {
        status: response.ok ? "SENT" : "FAILED",
        responseDetails: JSON.stringify(result),
      },
      create: {
        eventId: params.eventId,
        eventName: params.eventName,
        eventSource: "SERVER",
        orderId: params.customData?.order_id,
        status: response.ok ? "SENT" : "FAILED",
        responseDetails: JSON.stringify(result),
      },
    });

    return {
      success: response.ok,
      result,
    };
  } catch (error) {
    const errorMsg = error instanceof Error ? error.message : "Network error";
    await prisma.trackingEvent.upsert({
      where: { eventId: params.eventId },
      update: {
        status: "FAILED",
        responseDetails: JSON.stringify({ error: errorMsg }),
      },
      create: {
        eventId: params.eventId,
        eventName: params.eventName,
        eventSource: "SERVER",
        orderId: params.customData?.order_id,
        status: "FAILED",
        responseDetails: JSON.stringify({ error: errorMsg }),
      },
    });

    return {
      success: false,
      error: errorMsg,
    };
  }
}

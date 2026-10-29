import crypto from "crypto";
import Razorpay from "razorpay";
import { prisma } from "./prisma";

interface RazorpayConfig {
  keyId: string;
  keySecret: string;
  webhookSecret: string;
  mode: "test" | "live";
}

/**
 * Fetch dynamic Razorpay credentials from StoreSetting (or fallback to .env)
 */
export async function getRazorpayConfig(): Promise<RazorpayConfig> {
  const settings = await prisma.storeSetting.findMany({
    where: {
      key: {
        in: [
          "razorpay_key_id",
          "razorpay_key_secret",
          "razorpay_webhook_secret",
          "razorpay_mode",
        ],
      },
    },
  });

  const settingsMap = new Map(settings.map((s) => [s.key, s.value]));

  const keyId = settingsMap.get("razorpay_key_id") || process.env.RAZORPAY_KEY_ID || "rzp_test_placeholder";
  const keySecret = settingsMap.get("razorpay_key_secret") || process.env.RAZORPAY_KEY_SECRET || "rzp_test_secret_placeholder";
  const webhookSecret = settingsMap.get("razorpay_webhook_secret") || process.env.RAZORPAY_WEBHOOK_SECRET || "webhook_secret_placeholder";
  const mode = (settingsMap.get("razorpay_mode") as "test" | "live") || (process.env.RAZORPAY_MODE as "test" | "live") || "test";

  return { keyId, keySecret, webhookSecret, mode };
}

/**
 * Initialize Razorpay instance with server credentials
 */
export async function getRazorpayClient(): Promise<{ client: Razorpay; config: RazorpayConfig }> {
  const config = await getRazorpayConfig();
  const client = new Razorpay({
    key_id: config.keyId,
    key_secret: config.keySecret,
  });

  return { client, config };
}

/**
 * Create a Razorpay order on the server with exact minor units (paise)
 */
export async function createRazorpayOrder(params: {
  amountPaise: number;
  currency?: string;
  receipt: string;
  notes?: Record<string, string>;
}) {
  const { client, config } = await getRazorpayClient();

  // If in test mode with placeholder keys, simulate safe mock gateway order id
  if (config.keyId.includes("placeholder") || config.keySecret.includes("placeholder")) {
    return {
      id: `order_mock_${Date.now()}_${Math.random().toString(36).substring(2, 8)}`,
      entity: "order",
      amount: params.amountPaise,
      amount_paid: 0,
      amount_due: params.amountPaise,
      currency: params.currency || "INR",
      receipt: params.receipt,
      status: "created",
      attempts: 0,
      key_id: config.keyId,
      isMock: true,
    };
  }

  const order = await client.orders.create({
    amount: params.amountPaise,
    currency: params.currency || "INR",
    receipt: params.receipt,
    notes: params.notes,
  });

  return {
    ...order,
    key_id: config.keyId,
    isMock: false,
  };
}

/**
 * Verify Razorpay payment signature (HMAC-SHA256)
 */
export function verifyPaymentSignature(params: {
  orderId: string;
  paymentId: string;
  signature: string;
  secret: string;
}): boolean {
  // Allow test mock verification when placeholder is active
  if (params.secret.includes("placeholder") && params.orderId.startsWith("order_mock_")) {
    return true;
  }

  const generatedSignature = crypto
    .createHmac("sha256", params.secret)
    .update(`${params.orderId}|${params.paymentId}`)
    .digest("hex");

  return generatedSignature === params.signature;
}

/**
 * Verify Razorpay webhook signature using the raw request body
 */
export function verifyWebhookSignature(
  rawBody: string,
  signature: string,
  webhookSecret: string
): boolean {
  if (webhookSecret.includes("placeholder")) {
    return true;
  }

  const expectedSignature = crypto
    .createHmac("sha256", webhookSecret)
    .update(rawBody)
    .digest("hex");

  return expectedSignature === signature;
}

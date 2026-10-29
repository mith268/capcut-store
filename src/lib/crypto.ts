import crypto from "crypto";

const ALGORITHM = "aes-256-gcm";

function getEncryptionKey(): Buffer {
  const hex = process.env.ENCRYPTION_KEY || "0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef";
  return Buffer.from(hex, "hex");
}

export interface EncryptedPayload {
  encryptedData: string; // hex
  iv: string;            // hex
  authTag: string;       // hex
}

/**
 * Encrypt sensitive delivery credentials or secrets using AES-256-GCM
 */
export function encryptCredential(plainText: string): EncryptedPayload {
  const key = getEncryptionKey();
  const iv = crypto.randomBytes(12);
  const cipher = crypto.createCipheriv(ALGORITHM, key, iv);

  let encrypted = cipher.update(plainText, "utf8", "hex");
  encrypted += cipher.final("hex");
  const authTag = cipher.getAuthTag().toString("hex");

  return {
    encryptedData: encrypted,
    iv: iv.toString("hex"),
    authTag: authTag,
  };
}

/**
 * Decrypt sensitive credentials using AES-256-GCM
 */
export function decryptCredential(payload: EncryptedPayload): string {
  const key = getEncryptionKey();
  const decipher = crypto.createDecipheriv(
    ALGORITHM,
    key,
    Buffer.from(payload.iv, "hex")
  );

  decipher.setAuthTag(Buffer.from(payload.authTag, "hex"));

  let decrypted = decipher.update(payload.encryptedData, "hex", "utf8");
  decrypted += decipher.final("utf8");

  return decrypted;
}

/**
 * SHA-256 hash helper for order verification tokens and Meta CAPI normalization
 */
export function sha256Hash(value: string): string {
  return crypto.createHash("sha256").update(value.trim()).digest("hex");
}

/**
 * Format email for Meta Conversions API (lowercase, trimmed, SHA-256)
 */
export function hashForMeta(value: string): string {
  return crypto.createHash("sha256").update(value.trim().toLowerCase()).digest("hex");
}

/**
 * Generate a cryptographically secure random token (e.g. for order verification)
 */
export function generateSecureToken(bytes = 24): string {
  return crypto.randomBytes(bytes).toString("hex");
}

/**
 * Generate a human-readable order number, e.g. CS-94821
 */
export function generateOrderNumber(): string {
  const randomNum = Math.floor(10000 + Math.random() * 90000);
  return `CS-${randomNum}`;
}

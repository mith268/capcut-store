import jwt from "jsonwebtoken";
import { cookies } from "next/headers";
import { prisma } from "./prisma";

const JWT_SECRET = process.env.JWT_SECRET || "convora-jwt-secret-key-at-least-32-characters-long-2026";
const ADMIN_COOKIE_NAME = "convora_admin_token";

export interface AdminTokenPayload {
  userId: string;
  email: string;
  name: string;
  role: string;
}

export function signAdminToken(payload: AdminTokenPayload): string {
  return jwt.sign(payload, JWT_SECRET, { expiresIn: "7d" });
}

export function verifyAdminToken(token: string): AdminTokenPayload | null {
  try {
    return jwt.verify(token, JWT_SECRET) as AdminTokenPayload;
  } catch {
    return null;
  }
}

/**
 * Server-side helper to get the authenticated admin user from cookies
 */
export async function getAuthenticatedAdmin() {
  const cookieStore = cookies();
  const token = cookieStore.get(ADMIN_COOKIE_NAME)?.value;
  if (!token) return null;

  const payload = verifyAdminToken(token);
  if (!payload) return null;

  const user = await prisma.user.findUnique({
    where: { id: payload.userId },
    select: {
      id: true,
      name: true,
      email: true,
      role: true,
      mfaEnabled: true,
    },
  });

  return user;
}

/**
 * Check if the staff role has permissions for a given module
 */
export function hasPermission(
  role: string,
  permission: "ALL" | "CONTENT" | "ORDERS" | "INVENTORY" | "SECRETS" | "REPORTS"
): boolean {
  if (role === "OWNER" || role === "ADMIN") return true;

  switch (permission) {
    case "CONTENT":
      return role === "CONTENT_EDITOR";
    case "ORDERS":
      return role === "FULFILLMENT_STAFF" || role === "SUPPORT";
    case "INVENTORY":
      return role === "FULFILLMENT_STAFF";
    case "SECRETS":
      // Only Owner and Admin can access raw decryption or credentials
      return false;
    default:
      return false;
  }
}

export { ADMIN_COOKIE_NAME };
export const getStaffSession = getAuthenticatedAdmin;

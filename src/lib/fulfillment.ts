import { decryptCredential, encryptCredential, EncryptedPayload } from "./crypto";
import { prisma } from "./prisma";

export interface DecryptedCredential {
  emailOrUsername?: string;
  password?: string;
  activationCode?: string;
  activationLink?: string;
  downloadUrl?: string;
  notes?: string;
}

/**
 * Atomically assign an inventory item to a paid order
 */
export async function fulfillOrder(orderId: string): Promise<{
  success: boolean;
  status: "DELIVERED" | "MANUAL_PENDING" | "FAILED";
  message: string;
}> {
  return await prisma.$transaction(async (tx) => {
    const order = await tx.order.findUnique({
      where: { id: orderId },
      include: { plan: true, product: true },
    });

    if (!order) {
      return { success: false, status: "FAILED", message: "Order not found" };
    }

    // If order was already delivered, prevent duplicate fulfillment
    if (order.fulfillmentStatus === "DELIVERED") {
      return { success: true, status: "DELIVERED", message: "Order already fulfilled" };
    }

    const deliveryMethod = order.plan.deliveryMethod;

    // For manual delivery or account invites, mark as MANUAL_PENDING
    if (deliveryMethod === "MANUAL" || deliveryMethod === "ACCOUNT_INVITE") {
      await tx.order.update({
        where: { id: order.id },
        data: {
          fulfillmentStatus: "MANUAL_PENDING",
          updatedAt: new Date(),
        },
      });

      return {
        success: true,
        status: "MANUAL_PENDING",
        message: "Order placed for manual setup and delivery.",
      };
    }

    // For inventory-backed plans (CREDENTIALS, ACTIVATION_CODE, ACTIVATION_LINK)
    // Find an available inventory item for this specific plan
    const availableInventory = await tx.credentialInventory.findFirst({
      where: {
        planId: order.planId,
        status: "AVAILABLE",
      },
      orderBy: { createdAt: "asc" },
    });

    if (!availableInventory) {
      // Out of stock or late payment after stock exhausted:
      // Mark as MANUAL_PENDING so owner is alerted and customer is informed cleanly
      await tx.order.update({
        where: { id: order.id },
        data: {
          fulfillmentStatus: "MANUAL_PENDING",
          updatedAt: new Date(),
        },
      });

      return {
        success: true,
        status: "MANUAL_PENDING",
        message: "Item currently restocking. Fulfillment queued for prompt manual assignment.",
      };
    }

    // Atomically mark the inventory as ASSIGNED to this order
    const updatedInventory = await tx.credentialInventory.update({
      where: { id: availableInventory.id },
      data: {
        status: "ASSIGNED",
        orderId: order.id,
        deliveredAt: new Date(),
      },
    });

    // Create immutable fulfillment record
    await tx.fulfillmentRecord.create({
      data: {
        orderId: order.id,
        inventoryId: updatedInventory.id,
        deliveryMethod: deliveryMethod,
        encryptedDeliveredContent: updatedInventory.encryptedData,
        iv: updatedInventory.iv,
        authTag: updatedInventory.authTag,
      },
    });

    // Update order status to DELIVERED
    await tx.order.update({
      where: { id: order.id },
      data: {
        fulfillmentStatus: "DELIVERED",
        updatedAt: new Date(),
      },
    });

    // Decrement plan availableQuantity if finite
    if (order.plan.stockPolicy === "FINITE" && order.plan.availableQuantity > 0) {
      await tx.plan.update({
        where: { id: order.planId },
        data: {
          availableQuantity: { decrement: 1 },
        },
      });
    }

    return {
      success: true,
      status: "DELIVERED",
      message: "Order fulfilled successfully with digital access credentials.",
    };
  });
}

/**
 * Replace a delivered credential with a new one and record audit log
 */
export async function replaceOrderCredential(params: {
  orderId: string;
  reason: string;
  staffUserId: string;
}) {
  return await prisma.$transaction(async (tx) => {
    const order = await tx.order.findUnique({
      where: { id: params.orderId },
      include: { plan: true },
    });

    if (!order) throw new Error("Order not found");

    // Find new available inventory
    const newInventory = await tx.credentialInventory.findFirst({
      where: {
        planId: order.planId,
        status: "AVAILABLE",
      },
      orderBy: { createdAt: "asc" },
    });

    if (!newInventory) {
      throw new Error("No replacement inventory currently available in this plan");
    }

    // Mark previous inventory as RETIRED (never return to stock automatically)
    await tx.credentialInventory.updateMany({
      where: { orderId: order.id },
      data: { status: "RETIRED" },
    });

    // Assign new inventory
    await tx.credentialInventory.update({
      where: { id: newInventory.id },
      data: {
        status: "ASSIGNED",
        orderId: order.id,
        deliveredAt: new Date(),
      },
    });

    // Create replacement fulfillment record
    await tx.fulfillmentRecord.create({
      data: {
        orderId: order.id,
        inventoryId: newInventory.id,
        deliveryMethod: order.plan.deliveryMethod,
        encryptedDeliveredContent: newInventory.encryptedData,
        iv: newInventory.iv,
        authTag: newInventory.authTag,
        replacedReason: params.reason,
        replacedAt: new Date(),
        replacedByStaffId: params.staffUserId,
      },
    });

    // Record audit log
    await tx.auditLog.create({
      data: {
        userId: params.staffUserId,
        action: "REPLACE_CREDENTIAL",
        entityType: "ORDER",
        entityId: order.id,
        details: JSON.stringify({
          orderNumber: order.orderNumber,
          reason: params.reason,
          newInventoryId: newInventory.id,
        }),
      },
    });

    return { success: true };
  });
}

/**
 * Safely decrypt credentials for verified customer or authorized staff
 */
export function revealCredential(payload: EncryptedPayload): DecryptedCredential {
  const raw = decryptCredential(payload);
  try {
    return JSON.parse(raw);
  } catch {
    return { notes: raw };
  }
}

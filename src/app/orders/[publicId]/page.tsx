import { notFound } from "next/navigation";
import { Metadata } from "next";
import { prisma } from "@/lib/prisma";
import { sha256Hash } from "@/lib/crypto";
import { revealCredential, DecryptedCredential } from "@/lib/fulfillment";
import { getStaffSession } from "@/lib/auth";
import { StoreHeader } from "@/components/store/StoreHeader";
import { StoreFooter } from "@/components/store/StoreFooter";
import { OrderDeliveryClient } from "./OrderDeliveryClient";

interface OrderPageProps {
  params: { publicId: string };
  searchParams: { token?: string; email?: string };
}

export const dynamic = "force-dynamic";

export async function generateMetadata({
  params,
}: OrderPageProps): Promise<Metadata> {
  let order: any = null;

  try {
    order = await prisma.order.findUnique({
      where: { publicId: params.publicId },
      select: { orderNumber: true, product: { select: { title: true } } },
    });
  } catch {
    // Database may not be reachable during build
  }

  if (!order) return { title: "Order Not Found | CapCut Store" };

  return {
    title: `Order #${order.orderNumber} Delivery - ${order.product.title}`,
    robots: { index: false, follow: false },
  };
}

export default async function OrderDeliveryPage({
  params,
  searchParams,
}: OrderPageProps) {
  const { publicId } = params;
  const token = searchParams.token || "";
  const emailParam = searchParams.email || "";

  const order = await prisma.order.findUnique({
    where: { publicId },
    include: {
      plan: true,
      product: true,
      fulfillments: {
        orderBy: { createdAt: "desc" },
        take: 1,
      },
    },
  });

  if (!order) {
    notFound();
  }

  // Authorization check
  const staff = await getStaffSession();
  const tokenMatches = token && sha256Hash(token) === order.accessTokenHash;
  const emailMatches =
    emailParam &&
    emailParam.trim().toLowerCase() === order.customerEmail.toLowerCase();
  const isAuthorized = Boolean(tokenMatches || emailMatches || staff);

  // Decrypt credentials if authorized and available
  let credentials: DecryptedCredential | null = null;
  const latestFulfillment = order.fulfillments[0];

  if (isAuthorized && latestFulfillment?.encryptedDeliveredContent) {
    try {
      credentials = revealCredential({
        encryptedData: latestFulfillment.encryptedDeliveredContent,
        iv: latestFulfillment.iv || "",
        authTag: latestFulfillment.authTag || "",
      });
    } catch (e) {
      console.error("Failed to decrypt credentials for order", order.id, e);
    }
  }

  return (
    <div className="min-h-screen bg-[#05090F] text-slate-100 flex flex-col font-sans">
      <StoreHeader />

      <main className="flex-1 max-w-4xl w-full mx-auto px-4 py-10 md:py-14">
        <OrderDeliveryClient
          order={{
            publicId: order.publicId,
            orderNumber: order.orderNumber,
            customerName: order.customerName,
            customerEmail: order.customerEmail,
            customerPhone: order.customerPhone,
            productTitle: order.product.title,
            productSlug: order.product.slug,
            planName: order.plan.name,
            duration: order.plan.duration,
            unitPrice: order.unitPrice,
            discountAmount: order.discountAmount,
            totalAmount: order.totalAmount,
            currency: order.currency,
            paymentStatus: order.paymentStatus,
            fulfillmentStatus: order.fulfillmentStatus,
            bonusStatus: order.bonusStatus,
            bonusCustomerInput: order.bonusCustomerInput,
            paidAt: order.paidAt ? order.paidAt.toISOString() : null,
            deliveryMethod: order.plan.deliveryMethod,
            deliveryInstructions: order.plan.deliveryInstructions,
          }}
          credentials={credentials}
          isAuthorized={isAuthorized}
          initialToken={token}
        />
      </main>

      <StoreFooter />
    </div>
  );
}

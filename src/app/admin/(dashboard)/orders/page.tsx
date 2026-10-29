import { prisma } from "@/lib/prisma";
import { AdminOrdersClient } from "./AdminOrdersClient";

export const dynamic = "force-dynamic";

export default async function AdminOrdersPage() {
  const orders = await prisma.order.findMany({
    include: {
      product: { select: { title: true, slug: true } },
      plan: { select: { name: true, duration: true, deliveryMethod: true } },
    },
    orderBy: { createdAt: "desc" },
    take: 100,
  });

  return (
    <AdminOrdersClient initialOrders={JSON.parse(JSON.stringify(orders))} />
  );
}

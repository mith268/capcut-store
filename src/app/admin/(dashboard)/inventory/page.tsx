import { prisma } from "@/lib/prisma";
import { AdminInventoryClient } from "./AdminInventoryClient";

export const dynamic = "force-dynamic";

export default async function AdminInventoryPage() {
  const plans = await prisma.plan.findMany({
    where: { isActive: true },
    include: {
      product: { select: { id: true, title: true, slug: true } },
    },
    orderBy: [{ product: { title: "asc" } }, { displayOrder: "asc" }],
  });

  const stats = await Promise.all(
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

  const recent = await prisma.credentialInventory.findMany({
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

  const recentFormatted = recent.map((item) => ({
    id: item.id,
    planName: item.plan.name,
    productTitle: item.plan.product.title,
    status: item.status,
    identifier: item.plainHint || "Encrypted Entry",
    credentialType: "CREDENTIALS",
    createdAt: item.createdAt.toISOString(),
  }));

  const plansList = plans.map((p) => ({
    id: p.id,
    name: p.name,
    productTitle: p.product.title,
  }));

  return (
    <AdminInventoryClient
      initialStats={stats}
      initialRecent={recentFormatted}
      plansList={plansList}
    />
  );
}

import { prisma } from "@/lib/prisma";
import { AdminCouponsClient } from "./AdminCouponsClient";

export const dynamic = "force-dynamic";

export default async function AdminCouponsPage() {
  const coupons = await prisma.coupon.findMany({
    orderBy: { createdAt: "desc" },
  });

  return (
    <AdminCouponsClient initialCoupons={JSON.parse(JSON.stringify(coupons))} />
  );
}

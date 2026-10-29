import { prisma } from "@/lib/prisma";
import { AdminCategoriesClient } from "./AdminCategoriesClient";

export const dynamic = "force-dynamic";

export default async function AdminCategoriesPage() {
  const categories = await prisma.category.findMany({
    include: {
      _count: {
        select: { products: { where: { status: { not: "ARCHIVED" } } } },
      },
    },
    orderBy: { displayOrder: "asc" },
  });

  return (
    <AdminCategoriesClient initialCategories={JSON.parse(JSON.stringify(categories))} />
  );
}

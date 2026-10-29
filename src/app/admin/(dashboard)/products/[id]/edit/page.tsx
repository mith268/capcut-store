import { notFound } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { ProductEditClient } from "./ProductEditClient";

export const dynamic = "force-dynamic";

interface EditProductPageProps {
  params: { id: string };
}

export default async function AdminEditProductPage({ params }: EditProductPageProps) {
  const product = await prisma.product.findUnique({
    where: { id: params.id },
    include: {
      category: true,
      plans: {
        orderBy: { displayOrder: "asc" },
      },
      sections: {
        orderBy: { displayOrder: "asc" },
      },
    },
  });

  if (!product) {
    notFound();
  }

  const categories = await prisma.category.findMany({
    where: { isActive: true },
    select: { id: true, name: true },
    orderBy: { displayOrder: "asc" },
  });

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      <ProductEditClient
        initialProduct={JSON.parse(JSON.stringify(product))}
        categories={categories}
      />
    </div>
  );
}

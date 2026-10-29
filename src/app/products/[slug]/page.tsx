import { notFound, redirect } from "next/navigation";
import type { Metadata } from "next";
import { prisma } from "@/lib/prisma";
import { StoreHeader } from "@/components/store/StoreHeader";
import { StoreFooter } from "@/components/store/StoreFooter";
import { ProductLandingClient } from "@/components/product/ProductLandingClient";

interface Props {
  params: { slug: string };
  searchParams: Record<string, string | string[] | undefined>;
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const product = await prisma.product.findUnique({
    where: { slug: params.slug },
    include: { plans: true },
  });

  if (!product) return {};

  const lowestPlan = product.plans[0];
  const title = product.seoTitle || `${product.title} — Official Access & Instant Delivery`;
  const description =
    product.seoDescription ||
    product.shortDescription ||
    `Get verified ${product.title} with 30-day replacement warranty.`;

  return {
    title,
    description,
    alternates: {
      canonical: `/products/${product.slug}`,
    },
    openGraph: {
      title,
      description,
      images: product.socialImage ? [product.socialImage] : undefined,
    },
  };
}

export default async function ProductDetailPage({ params }: Props) {
  // Check for slug redirects first
  const redirectRecord = await prisma.productSlugRedirect.findUnique({
    where: { oldSlug: params.slug },
  });

  if (redirectRecord) {
    redirect(`/products/${redirectRecord.newSlug}`);
  }

  // Fetch product with plans and enabled sections
  const product = await prisma.product.findUnique({
    where: { slug: params.slug },
    include: {
      plans: {
        where: { isActive: true },
        orderBy: { displayOrder: "asc" },
      },
      sections: {
        where: { isEnabled: true },
        orderBy: { displayOrder: "asc" },
      },
    },
  });

  if (!product || product.status === "ARCHIVED") {
    notFound();
  }

  // Store settings
  const settings = await prisma.storeSetting.findMany();
  const settingsMap = new Map(settings.map((s) => [s.key, s.value]));
  const storeName = settingsMap.get("store_name") || "Convora Store";
  const storeBadge = settingsMap.get("store_badge") || "VERIFIED";
  const whatsappNumber = settingsMap.get("whatsapp_number") || "919990798996";

  return (
    <>
      <StoreHeader
        storeName={storeName}
        storeBadge={storeBadge}
        whatsappNumber={whatsappNumber}
      />

      <main>
        <ProductLandingClient
          product={product}
          plans={product.plans}
          sections={product.sections}
          whatsappNumber={whatsappNumber}
          storeName={storeName}
        />
      </main>

      <StoreFooter storeName={storeName} />
    </>
  );
}

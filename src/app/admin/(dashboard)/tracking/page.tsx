import { prisma } from "@/lib/prisma";
import { getMetaConfig } from "@/lib/meta-tracking";
import { AdminTrackingClient } from "./AdminTrackingClient";

export const dynamic = "force-dynamic";

export default async function AdminTrackingPage() {
  const config = await getMetaConfig();

  const recentEvents = await prisma.trackingEvent.findMany({
    orderBy: { createdAt: "desc" },
    take: 25,
  });

  return (
    <AdminTrackingClient
      config={{
        pixelId: config.pixelId,
        hasAccessToken: Boolean(config.accessToken),
        testEventCode: config.testEventCode,
        isEnabled: config.isEnabled,
      }}
      recentEvents={JSON.parse(JSON.stringify(recentEvents))}
    />
  );
}

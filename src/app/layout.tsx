import type { Metadata } from "next";
import { Inter, JetBrains_Mono } from "next/font/google";
import Script from "next/script";
import "./globals.css";
import { prisma } from "@/lib/prisma";

const inter = Inter({
  subsets: ["latin"],
  variable: "--font-inter",
  display: "swap",
});

const jetbrainsMono = JetBrains_Mono({
  subsets: ["latin"],
  variable: "--font-mono",
  display: "swap",
});

export async function generateMetadata(): Promise<Metadata> {
  let storeName = "Convora Store";
  let storeTagline = "Premium Digital Tools & Verified Subscriptions";

  try {
    const storeNameSetting = await prisma.storeSetting.findUnique({
      where: { key: "store_name" },
    });
    const storeTaglineSetting = await prisma.storeSetting.findUnique({
      where: { key: "store_tagline" },
    });

    if (storeNameSetting?.value) storeName = storeNameSetting.value;
    if (storeTaglineSetting?.value) storeTagline = storeTaglineSetting.value;
  } catch {
    // Database may not be reachable or initialized during build time
  }

  return {
    title: {
      template: `%s | ${storeName}`,
      default: `${storeName} — ${storeTagline}`,
    },
    description: storeTagline,
    metadataBase: new URL(process.env.APP_URL || "http://localhost:3000"),
    icons: {
      icon: "/favicon.ico",
    },
    openGraph: {
      type: "website",
      siteName: storeName,
      title: `${storeName} — ${storeTagline}`,
      description: "Get verified digital software, creator subscriptions, and license keys with instant digital delivery and replacement warranty.",
    },
  };
}

export default async function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  let pixelId: string | undefined = undefined;
  let isTrackingEnabled = true;

  try {
    const pixelIdSetting = await prisma.storeSetting.findUnique({
      where: { key: "meta_pixel_id" },
    });
    const trackingEnabledSetting = await prisma.storeSetting.findUnique({
      where: { key: "meta_tracking_enabled" },
    });

    pixelId = pixelIdSetting?.value;
    isTrackingEnabled = (trackingEnabledSetting?.value ?? "true") === "true";
  } catch {
    // Database may not be reachable or initialized during build time
  }

  return (
    <html lang="en" className={`${inter.variable} ${jetbrainsMono.variable} scroll-smooth`}>
      <head>
        {/* Razorpay Checkout SDK */}
        <Script
          src="https://checkout.razorpay.com/v1/checkout.js"
          strategy="lazyOnload"
        />

        {/* Browser Meta Pixel (when configured and enabled) */}
        {isTrackingEnabled && pixelId && (
          <Script id="meta-pixel" strategy="afterInteractive">
            {`
              !function(f,b,e,v,n,t,s)
              {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
              n.callMethod.apply(n,arguments):n.queue.push(arguments)};
              if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
              n.queue=[];t=b.createElement(e);t.async=!0;
              t.src=v;s=b.getElementsByTagName(e)[0];
              s.parentNode.insertBefore(t,s)}(window, document,'script',
              'https://connect.facebook.net/en_US/fbevents.js');
              fbq('init', '${pixelId}');
              fbq('track', 'PageView');
            `}
          </Script>
        )}
      </head>
      <body className="min-h-screen flex flex-col font-sans antialiased bg-[#F8FAFC] text-main-text selection:bg-blue-100 selection:text-blue-900">
        {children}
      </body>
    </html>
  );
}

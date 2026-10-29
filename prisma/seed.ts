import { PrismaClient } from "@prisma/client";
import bcrypt from "bcryptjs";
import crypto from "crypto";

const prisma = new PrismaClient();

const ENCRYPTION_KEY = Buffer.from(
  process.env.ENCRYPTION_KEY || "0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef",
  "hex"
);

function encryptSeed(plainText: string) {
  const iv = crypto.randomBytes(12);
  const cipher = crypto.createCipheriv("aes-256-gcm", ENCRYPTION_KEY, iv);
  let encrypted = cipher.update(plainText, "utf8", "hex");
  encrypted += cipher.final("hex");
  const authTag = cipher.getAuthTag().toString("hex");

  return {
    encryptedData: encrypted,
    iv: iv.toString("hex"),
    authTag: authTag,
  };
}

async function main() {
  console.log("🌱 Starting database seed...");

  // 1. Admin User
  const passwordHash = await bcrypt.hash("Admin@CapCut2026!", 10);
  await prisma.user.upsert({
    where: { email: "admin@capcutstore.local" },
    update: { passwordHash, role: "OWNER" },
    create: {
      name: "Store Administrator",
      email: "admin@capcutstore.local",
      passwordHash,
      role: "OWNER",
      mfaEnabled: false,
    },
  });
  console.log("✓ Admin user created: admin@capcutstore.local");

  // 2. Categories
  const catVideo = await prisma.category.upsert({
    where: { slug: "video-audio-tools" },
    update: {},
    create: {
      name: "Video & Audio Tools",
      slug: "video-audio-tools",
      description: "Professional creative video editing software and subscriptions",
      displayOrder: 1,
    },
  });

  const catDesign = await prisma.category.upsert({
    where: { slug: "design-graphics" },
    update: {},
    create: {
      name: "Design & Graphics",
      slug: "design-graphics",
      description: "Templates, Brand Kits, stock assets, and graphic design software",
      displayOrder: 2,
    },
  });

  const catAI = await prisma.category.upsert({
    where: { slug: "ai-subscriptions" },
    update: {},
    create: {
      name: "AI & Subscriptions",
      slug: "ai-subscriptions",
      description: "Advanced generative AI tools, coding assistants, and workspace models",
      displayOrder: 3,
    },
  });

  // 3. Product 1: CapCut Pro
  const capcut = await prisma.product.upsert({
    where: { slug: "capcut-pro" },
    update: {},
    create: {
      title: "CapCut Pro (1 Month)",
      slug: "capcut-pro",
      shortTitle: "CapCut Pro",
      subtitle: "1200 AI Credits • Full Pro Access • Windows, Mac, iOS & Android",
      shortDescription: "Get official CapCut Pro access for 1 month with 1200 AI credits, 4K 60fps export, optical flow speed ramping, cloud storage, and replacement warranty.",
      longDescription: "Unleash your video editing potential with official CapCut Pro access. Unlock all premium AI features including Auto Captions, Text-to-Speech voices, Camera Tracking, Background Removal, 4K Ultra HD Export, and thousands of trending transitions and sound effects.",
      categoryId: catVideo.id,
      tags: "capcut, video editing, ai credits, reels, tiktok, youtube",
      thumbnail: "/assets/images/capcut-thumb.png",
      videoUrl: "https://www.youtube.com/watch?v=GeXVI3p-BqE",
      videoProvider: "youtube",
      videoId: "GeXVI3p-BqE",
      status: "PUBLISHED",
      visibility: "PUBLIC",
      isFeatured: true,
      displayOrder: 1,
      seoTitle: "Buy CapCut Pro 1 Month (1200 AI Credits) — Instant Delivery",
      seoDescription: "Get genuine CapCut Pro access with 1200 AI credits and 30-day warranty. Instant digital delivery and 24/7 WhatsApp support.",
      themePrimaryColor: "#00C8F4",
      themeAccentColor: "#1677FF",
      themePreset: "dark",
      bonusEnabled: true,
      bonusTitle: "🎁 BONUS: CANVA PRO FREE (1 YEAR)",
      bonusBadge: "VALUED AT ₹1,499",
      bonusSubtitle: "Complete your CapCut Pro purchase and claim your Canva Pro 1-year bonus on your personal email.",
      bonusExplanation: "Canva Pro activation is completed on your personal email ID with no existing password required. Provide your email after payment inside the secure order portal.",
      bonusImage: "/assets/images/canva-pro-sticker.png",
      bonusClaimType: "EMAIL_INPUT",
      bonusDeliveryMethod: "MANUAL",
      stickyBarEnabled: true,
      stickyBarText: "CapCut Pro • 1200 AI Credits + Canva Pro FREE",
      stickyBarButtonLabel: "Buy CapCut Pro",
    },
  });

  // CapCut Plans
  const capcutPlan1 = await prisma.plan.upsert({
    where: { sku: "CP-1M-1200" },
    update: {},
    create: {
      productId: capcut.id,
      name: "1 Month • 1200 AI Credits",
      sku: "CP-1M-1200",
      duration: "1 Month",
      price: 29900, // ₹299
      compareAtPrice: 99900, // ₹999
      currency: "INR",
      benefits: JSON.stringify([
        "1200 AI Credits Included",
        "2 Devices Supported (PC/Mac/Phone)",
        "4K 60FPS Video Export",
        "Auto Captions & AI Translation",
        "30-Day Full Replacement Warranty",
        "Instant Digital Delivery",
      ]),
      restrictions: JSON.stringify(["Do not change assigned master profile email or password"]),
      deviceLimit: 2,
      deliveryMethod: "CREDENTIALS",
      deliveryInstructions: "Connect your device to VPN if in a regionally restricted area, open CapCut, sign in with the email & password provided below, and click verify.",
      stockPolicy: "FINITE",
      availableQuantity: 5,
      isDefault: true,
      isRecommended: true,
      recommendedLabel: "MOST POPULAR",
      displayOrder: 1,
    },
  });

  const capcutPlan2 = await prisma.plan.upsert({
    where: { sku: "CP-3M-3600" },
    update: {},
    create: {
      productId: capcut.id,
      name: "3 Months • 3600 AI Credits",
      sku: "CP-3M-3600",
      duration: "3 Months",
      price: 69900, // ₹699
      compareAtPrice: 249900, // ₹2,499
      currency: "INR",
      benefits: JSON.stringify([
        "3600 AI Credits (1200/mo)",
        "2 Devices Supported",
        "All Pro Transitions & Effects",
        "90-Day Full Replacement Guarantee",
        "Instant Delivery",
      ]),
      deviceLimit: 2,
      deliveryMethod: "CREDENTIALS",
      stockPolicy: "FINITE",
      availableQuantity: 4,
      isDefault: false,
      isRecommended: false,
      displayOrder: 2,
    },
  });

  // CapCut Landing Page Sections
  const capcutSections = [
    {
      sectionType: "ANNOUNCEMENT",
      title: "🔥 LIMITED-TIME OFFER • 1200 AI CREDITS + CANVA PRO 1 YEAR FREE • 30-DAY WARRANTY",
      displayOrder: 1,
      content: JSON.stringify({
        badge: "VERIFIED OFFER",
        linkText: "Get Access Now",
      }),
    },
    {
      sectionType: "HERO",
      title: "CapCut Pro 1 Month",
      subtitle: "1200 AI Credits • Instant Delivery • 30-Day Warranty",
      badgeText: "LIMITED-TIME DIGITAL OFFER",
      displayOrder: 2,
      content: JSON.stringify({
        kicker: "⚡ VERIFIED DIGITAL ACCESS",
        headline: "CapCut Pro 1 Month",
        highlightedText: "1200 AI Credits",
        bonusLine: "🎁 Includes Canva Pro 1 Year FREE Bonus",
        primaryCta: "BUY NOW",
        whatsappCta: "WHATSAPP",
        videoTitle: "CapCut Pro Video Overview",
        videoId: "GeXVI3p-BqE",
      }),
    },
    {
      sectionType: "KEY_FACTS",
      title: "Core Specifications",
      displayOrder: 3,
      content: JSON.stringify({
        chips: [
          { icon: "Sparkles", text: "1200 AI Credits" },
          { icon: "Laptop", text: "Windows & Mac Supported" },
          { icon: "Smartphone", text: "iOS & Android" },
          { icon: "Users", text: "2 Devices Login" },
          { icon: "ShieldCheck", text: "30-Day Guarantee" },
          { icon: "Zap", text: "Instant Delivery" },
        ],
      }),
    },
    {
      sectionType: "BENEFITS",
      title: "Everything You Need to Edit Like a Pro",
      subtitle: "Unrestricted access to all studio tools, effects, and generative AI features.",
      displayOrder: 4,
      content: JSON.stringify({
        items: [
          {
            title: "Auto Captions & Subtitles",
            description: "Generate animated captions in seconds with 99% accuracy across 20+ languages.",
            icon: "Subtitles",
          },
          {
            title: "4K 60FPS Ultra HD Export",
            description: "Export crisp, cinematic videos with zero compression artifacts and no watermark.",
            icon: "Film",
          },
          {
            title: "Optical Flow & Speed Ramping",
            description: "Create buttery smooth super-slow motion clips using intelligent AI frame interpolation.",
            icon: "Gauge",
          },
          {
            title: "Background Removal & Chroma Key",
            description: "One-click background cutout without a green screen, powered by smart portrait segmentation.",
            icon: "Scissors",
          },
        ],
      }),
    },
    {
      sectionType: "HOW_IT_WORKS",
      title: "Tutorial Steps to Login to CapCut",
      subtitle: "Follow these simple steps to connect, sign in, verify your access, and start editing.",
      displayOrder: 5,
      content: JSON.stringify({
        steps: [
          {
            number: "01",
            title: "Download & Connect VPN",
            description: "Download a VPN application and connect to an active server before opening CapCut if regional access requires it.",
            badge: "STEP 1",
          },
          {
            number: "02",
            title: "Download CapCut",
            description: "Download and install the official CapCut desktop or mobile application on your device.",
            badge: "STEP 2",
          },
          {
            number: "03",
            title: "Login With Provided Account",
            description: "Sign in using the dedicated login credentials delivered to your secure order page.",
            badge: "STEP 3",
          },
          {
            number: "04",
            title: "Verify Access & Start Editing",
            description: "Check your Pro badge status in settings, enjoy 1200 AI credits, and start creating.",
            badge: "STEP 4",
          },
        ],
      }),
    },
    {
      sectionType: "BONUS",
      title: "🎁 BONUS: CANVA PRO FREE (1 YEAR)",
      subtitle: "1 Year Canva Pro Personal Activation Included",
      badgeText: "VALUED AT ₹1,499",
      displayOrder: 6,
      content: JSON.stringify({
        explanation: "Unlock 100M+ premium stock photos, brand kits, background remover, and cloud storage. Claim your bonus after payment by submitting your Canva email inside your order portal.",
        buttonText: "Claim After Checkout",
      }),
    },
    {
      sectionType: "FAQS",
      title: "Frequently Asked Questions",
      subtitle: "Clear answers before you purchase.",
      displayOrder: 7,
      content: JSON.stringify({
        faqs: [
          {
            q: "How many devices can I use simultaneously?",
            a: "Our CapCut Pro 1-month plan supports login on up to 2 devices (e.g. your PC and your phone).",
          },
          {
            q: "Do I need a VPN to use CapCut?",
            a: "In countries where CapCut web or servers are regionally restricted (such as India), connecting to a free or paid VPN server is required to sign in and fetch effects.",
          },
          {
            q: "How soon do I receive my credentials?",
            a: "Immediately! As soon as your Razorpay payment verifies, your credentials appear instantly on your secure order page and are emailed to you.",
          },
          {
            q: "What does the 30-day warranty cover?",
            a: "If your account logs out or faces any licensing issue during your 30-day period, our 24/7 WhatsApp team will immediately provide a replacement login.",
          },
        ],
      }),
    },
  ];

  for (const s of capcutSections) {
    await prisma.landingPageSection.create({
      data: {
        productId: capcut.id,
        sectionType: s.sectionType,
        title: s.title,
        subtitle: s.subtitle,
        badgeText: s.badgeText,
        content: s.content,
        displayOrder: s.displayOrder,
        isEnabled: true,
      },
    });
  }

  // Seed 3 Fake Demo Credentials for CapCut Plan 1
  for (let i = 1; i <= 3; i++) {
    const credData = JSON.stringify({
      emailOrUsername: `capcut_user${i}@example.test`,
      password: `PassCapCut!${i}_9482`,
      instructions: "Sign in with email and password provided. Do not modify account recovery settings.",
    });
    const enc = encryptSeed(credData);

    await prisma.credentialInventory.create({
      data: {
        planId: capcutPlan1.id,
        status: "AVAILABLE",
        encryptedData: enc.encryptedData,
        iv: enc.iv,
        authTag: enc.authTag,
        plainHint: `capcut_user${i}@***.test`,
        setupInstructions: "Download CapCut, connect VPN, sign in, verify Pro badge.",
      },
    });
  }
  console.log("✓ CapCut Pro seeded with plans, sections, and demo inventory");

  // 4. Product 2: Canva Pro
  const canva = await prisma.product.upsert({
    where: { slug: "canva-pro" },
    update: {},
    create: {
      title: "Canva Pro (1 Year License)",
      slug: "canva-pro",
      shortTitle: "Canva Pro",
      subtitle: "100M+ Stock Assets • Brand Kit • Background Remover • 100GB Storage",
      shortDescription: "Get official 1-year Canva Pro access activated directly on your personal email address. Unlimited templates, premium fonts, Magic Studio AI tools, and Brand Kit.",
      longDescription: "Design anything from social media graphics to presentations, videos, and print merchandise. Unlimited premium content, 100M+ photos, audio tracks, videos, and full Magic Resize capabilities.",
      categoryId: catDesign.id,
      tags: "canva, graphics, brand kit, templates, social media, design",
      thumbnail: "/assets/images/canva-thumb.png",
      status: "PUBLISHED",
      visibility: "PUBLIC",
      isFeatured: true,
      displayOrder: 2,
      seoTitle: "Buy Canva Pro 1 Year License — Instant Personal Activation",
      seoDescription: "Official Canva Pro 1 Year activation on your personal email. Zero password sharing required.",
      themePrimaryColor: "#7C3AED",
      themeAccentColor: "#00C8F4",
      themePreset: "dark",
      bonusEnabled: false,
      stickyBarEnabled: true,
      stickyBarText: "Canva Pro 1 Year License • ₹299",
      stickyBarButtonLabel: "Get Canva Pro",
    },
  });

  const canvaPlan1 = await prisma.plan.upsert({
    where: { sku: "CNV-1Y-PERS" },
    update: {},
    create: {
      productId: canva.id,
      name: "1 Year Personal Activation",
      sku: "CNV-1Y-PERS",
      duration: "12 Months",
      price: 29900, // ₹299
      compareAtPrice: 149900,
      currency: "INR",
      benefits: JSON.stringify([
        "Activated on Your Own Email",
        "100M+ Premium Stock Photos & Videos",
        "Brand Kit with Custom Fonts & Logos",
        "Magic Background Remover",
        "100GB Cloud Storage",
        "1 Year Full Replacement Warranty",
      ]),
      deviceLimit: 5,
      deliveryMethod: "ACCOUNT_INVITE",
      deliveryInstructions: "Provide your Canva account email address after checkout on your order page. You will receive an official team invitation link to upgrade your account.",
      stockPolicy: "UNLIMITED",
      availableQuantity: 100,
      isDefault: true,
      isRecommended: true,
      displayOrder: 1,
    },
  });

  // Canva Sections
  await prisma.landingPageSection.createMany({
    data: [
      {
        productId: canva.id,
        sectionType: "HERO",
        title: "Canva Pro 1 Year License",
        subtitle: "Direct personal activation on your existing email ID",
        badgeText: "OFFICIAL ACTIVATION",
        content: JSON.stringify({
          kicker: "🎨 DESIGN WITHOUT LIMITS",
          headline: "Canva Pro 1 Year License",
          highlightedText: "Activated on Your Own Email",
          primaryCta: "BUY CANVA PRO",
          whatsappCta: "WHATSAPP",
        }),
        displayOrder: 1,
      },
      {
        productId: canva.id,
        sectionType: "BENEFITS",
        title: "Why Creators Love Canva Pro",
        displayOrder: 2,
        content: JSON.stringify({
          items: [
            { title: "One-Click Magic Background Remover", description: "Cut out portraits and product shots in one tap.", icon: "Sparkles" },
            { title: "Brand Kit & Custom Logos", description: "Store your color palettes, fonts, and logos for easy client styling.", icon: "Palette" },
            { title: "100M+ Stock Assets", description: "Never pay for stock photos, graphics, or audio again.", icon: "Image" },
          ],
        }),
      },
      {
        productId: canva.id,
        sectionType: "FAQS",
        title: "Canva Pro Questions",
        displayOrder: 3,
        content: JSON.stringify({
          faqs: [
            { q: "Do I need to share my Canva password?", a: "Never! We only need your Canva email to send you the official Pro invitation link." },
            { q: "What happens to my existing designs?", a: "All your existing designs and folders remain completely intact and untouched." },
          ],
        }),
      },
    ],
  });
  console.log("✓ Canva Pro seeded");

  // 5. Product 3: Gemini Advanced (AI Studio)
  const gemini = await prisma.product.upsert({
    where: { slug: "gemini-ai" },
    update: {},
    create: {
      title: "Google Gemini Advanced (1 Month)",
      slug: "gemini-ai",
      shortTitle: "Gemini Advanced",
      subtitle: "Gemini 1.5 Pro & Flash with 1 Million Token Context Window",
      shortDescription: "Experience Google's most capable AI model with 1M token context, deep document analysis, multimodal video parsing, and coding capabilities.",
      longDescription: "Analyze massive PDFs, full codebases, and hour-long videos in a single prompt. Integrated with Google Workspace and Python code execution.",
      categoryId: catAI.id,
      tags: "gemini, ai, google, 1m context, python, coding",
      thumbnail: "/assets/images/gemini-thumb.png",
      status: "PUBLISHED",
      visibility: "PUBLIC",
      isFeatured: true,
      displayOrder: 3,
      seoTitle: "Buy Gemini Advanced 1 Month Access — 1M Context Window",
      seoDescription: "Google Gemini Advanced subscription with 1.5 Pro and 1M tokens context. Fast digital delivery.",
      themePrimaryColor: "#2563EB",
      themeAccentColor: "#00C8F4",
      themePreset: "dark",
      bonusEnabled: false,
      stickyBarEnabled: true,
      stickyBarText: "Gemini Advanced • 1M Context Window • ₹499",
      stickyBarButtonLabel: "Get Gemini Advanced",
    },
  });

  const geminiPlan = await prisma.plan.upsert({
    where: { sku: "GEM-1M-ADV" },
    update: {},
    create: {
      productId: gemini.id,
      name: "1 Month Dedicated Access",
      sku: "GEM-1M-ADV",
      duration: "1 Month",
      price: 49900, // ₹499
      compareAtPrice: 199900,
      currency: "INR",
      benefits: JSON.stringify([
        "Gemini 1.5 Pro with 1M Tokens Context",
        "Upload 1,500 Page PDFs & Video Files",
        "Python Code Execution Sandbox",
        "Google Workspace Integration",
        "30-Day Guarantee",
      ]),
      deviceLimit: 2,
      deliveryMethod: "CREDENTIALS",
      stockPolicy: "FINITE",
      availableQuantity: 3,
      isDefault: true,
      isRecommended: true,
      displayOrder: 1,
    },
  });

  for (let i = 1; i <= 2; i++) {
    const credData = JSON.stringify({
      emailOrUsername: `gemini_pro${i}@example.test`,
      password: `PassGemini!${i}_5739`,
      notes: "Log in via Google account credentials provided.",
    });
    const enc = encryptSeed(credData);

    await prisma.credentialInventory.create({
      data: {
        planId: geminiPlan.id,
        status: "AVAILABLE",
        encryptedData: enc.encryptedData,
        iv: enc.iv,
        authTag: enc.authTag,
        plainHint: `gemini_pro${i}@***.test`,
        setupInstructions: "Visit gemini.google.com and sign in with the provided account.",
      },
    });
  }
  console.log("✓ Gemini AI seeded");

  // 6. Coupons
  await prisma.coupon.upsert({
    where: { code: "SAVE50" },
    update: {},
    create: {
      code: "SAVE50",
      discountType: "FIXED",
      discountValue: 5000, // ₹50 in paise
      minOrderAmount: 20000, // ₹200 min
      isActive: true,
      usageLimit: 500,
    },
  });

  await prisma.coupon.upsert({
    where: { code: "PRO10" },
    update: {},
    create: {
      code: "PRO10",
      discountType: "PERCENTAGE",
      discountValue: 10, // 10%
      minOrderAmount: 10000,
      maxDiscountAmount: 30000, // max ₹300 discount
      isActive: true,
      usageLimit: 1000,
    },
  });
  console.log("✓ Coupons seeded (SAVE50, PRO10)");

  // 7. Store Settings
  const storeSettings = [
    { key: "store_name", value: "Convora Store", group: "branding" },
    { key: "store_tagline", value: "Premium Digital Tools & Verified Subscriptions", group: "branding" },
    { key: "store_badge", value: "VERIFIED", group: "branding" },
    { key: "support_email", value: "support@convora.store", group: "general" },
    { key: "whatsapp_number", value: "919990798996", group: "general" },
    { key: "default_warranty_days", value: "30", group: "policies" },
    { key: "razorpay_mode", value: "test", group: "payment" },
    { key: "razorpay_key_id", value: "rzp_test_placeholder", group: "payment" },
    { key: "razorpay_key_secret", value: "rzp_test_secret_placeholder", group: "payment" },
    { key: "meta_tracking_enabled", value: "false", group: "tracking" },
  ];

  for (const s of storeSettings) {
    await prisma.storeSetting.upsert({
      where: { key: s.key },
      update: { value: s.value, group: s.group },
      create: s,
    });
  }
  console.log("✓ Store settings seeded");

  console.log("🎉 Database seeding completed successfully!");
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });

import { prisma } from "../src/lib/prisma";
import { encryptCredential } from "../src/lib/crypto";

async function seedEbooks() {
  console.log("Seeding Health & Wellness eBooks for Razorpay merchant verification...");

  // 1. Create eBook Categories
  const catSpine = await prisma.category.upsert({
    where: { slug: "pain-relief-spine" },
    update: {
      name: "Spine & Pain Relief",
      description: "Physiotherapist-designed digital protocols for neck, shoulder, and back relief",
      displayOrder: 1,
      isActive: true,
    },
    create: {
      name: "Spine & Pain Relief",
      slug: "pain-relief-spine",
      description: "Physiotherapist-designed digital protocols for neck, shoulder, and back relief",
      displayOrder: 1,
      isActive: true,
    },
  });

  const catGut = await prisma.category.upsert({
    where: { slug: "gut-health-digestion" },
    update: {
      name: "Gut Health & Digestion",
      description: "Science-backed nutritional guides to restore digestion and gut microbiome",
      displayOrder: 2,
      isActive: true,
    },
    create: {
      name: "Gut Health & Digestion",
      slug: "gut-health-digestion",
      description: "Science-backed nutritional guides to restore digestion and gut microbiome",
      displayOrder: 2,
      isActive: true,
    },
  });

  const catPosture = await prisma.category.upsert({
    where: { slug: "posture-ergonomics" },
    update: {
      name: "Posture & Ergonomics",
      description: "Workstation ergonomics and anti-slouch corrective alignment blueprints",
      displayOrder: 3,
      isActive: true,
    },
    create: {
      name: "Posture & Ergonomics",
      slug: "posture-ergonomics",
      description: "Workstation ergonomics and anti-slouch corrective alignment blueprints",
      displayOrder: 3,
      isActive: true,
    },
  });

  const catSleep = await prisma.category.upsert({
    where: { slug: "sleep-stress" },
    update: {
      name: "Sleep & Stress Wellness",
      description: "Natural sleep hygiene and nighttime cortisol reduction protocols",
      displayOrder: 4,
      isActive: true,
    },
    create: {
      name: "Sleep & Stress Wellness",
      slug: "sleep-stress",
      description: "Natural sleep hygiene and nighttime cortisol reduction protocols",
      displayOrder: 4,
      isActive: true,
    },
  });

  // 2. eBook Products Data
  const ebooks = [
    {
      title: "Cervical Pain Relief & Neck Mobility Guide",
      slug: "cervical-pain-relief-guide",
      shortTitle: "Cervical Pain Relief Guide",
      subtitle: "30-Day Step-by-Step Neck Decompression & Posture Correction Protocol",
      shortDescription: "Evidence-based 15-minute daily physiotherapy drills, sleep alignment adjustments, and nerve relief routines to eliminate chronic cervical stiffness and tension headaches.",
      longDescription: "The Cervical Pain Relief & Neck Mobility Guide provides a structured 30-day clinical roadmap to help desk workers, students, and active adults release trapped cervical nerves, correct forward-head posture, and restore pain-free neck rotation without expensive equipment.",
      categoryId: catSpine.id,
      priceINR: 199,
      comparePriceINR: 499,
      sku: "EBOOK-CERVICAL-01",
      planName: "Instant Digital Download (Complete PDF Edition)",
      benefits: [
        "30-Day Step-by-Step Neck & Shoulder Protocol",
        "Pillow & Sleep Ergonomics Adjustment Guide",
        "Instant Downloadable PDF + Lifetime Access",
        "100% Satisfaction & Replacement Guarantee",
      ],
      downloadUrl: "https://storage.convora.local/downloads/cervical-pain-relief-guide.pdf",
      isFeatured: true,
      displayOrder: 1,
    },
    {
      title: "20-Day Gut Issues Fix & Digestion Reset Guide",
      slug: "gut-issues-fix-guide-20-days",
      shortTitle: "20-Day Gut Issues Fix Guide",
      subtitle: "Complete Microbiome Restoration & Anti-Bloat Protocol",
      shortDescription: "A clinical nutrition roadmap to eliminate acid reflux, chronic bloating, IBS discomfort, and sluggish digestion through targeted whole foods and elimination protocols.",
      longDescription: "Reclaim optimal digestive health with our 20-Day Gut Reset Guide. Designed by holistic nutritionists, this protocol walks you day-by-day through soothing gut lining inflammation, replenishing essential microflora, and identifying personal food sensitivities.",
      categoryId: catGut.id,
      priceINR: 249,
      comparePriceINR: 599,
      sku: "EBOOK-GUT-20DAY-01",
      planName: "Instant Digital Download (Complete PDF Edition)",
      benefits: [
        "20-Day Day-by-Day Gut Rebuilding Timetable",
        "Anti-Bloat Recipe Book & Grocery Checklist",
        "Natural Digestive Teas & Probiotic Protocols",
        "Instant PDF Download for Phone & PC",
      ],
      downloadUrl: "https://storage.convora.local/downloads/gut-issues-fix-guide-20-days.pdf",
      isFeatured: true,
      displayOrder: 2,
    },
    {
      title: "Lower Back Mobility & Sciatica Freedom Handbook",
      slug: "lower-back-sciatica-guide",
      shortTitle: "Lower Back & Sciatica Guide",
      subtitle: "Gentle Spinal Decompression & Pelvic Realignment Blueprint",
      shortDescription: "Physical therapist-designed decompression stretches to relieve chronic lumbar pain, tight hips, and shooting sciatic nerve pressure safely from home.",
      longDescription: "Overcome debilitating lower back stiffness and sciatic nerve flare-ups. This illustrated handbook breaks down gentle non-invasive stretches that decompress compressed lumbar vertebrae and strengthen supporting deep core stabilizers.",
      categoryId: catSpine.id,
      priceINR: 199,
      comparePriceINR: 399,
      sku: "EBOOK-LOWERBACK-01",
      planName: "Instant Digital Download (Complete PDF Edition)",
      benefits: [
        "10-Minute Daily Decompression Stretch Routine",
        "Safe Bending, Sitting & Lifting Body Mechanics",
        "Pelvic Tilt & Core Stability Exercise Library",
        "Instant Downloadable PDF Format",
      ],
      downloadUrl: "https://storage.convora.local/downloads/lower-back-sciatica-guide.pdf",
      isFeatured: false,
      displayOrder: 3,
    },
    {
      title: "Desk Worker Posture Correction & Anti-Slouch Blueprint",
      slug: "desk-worker-posture-guide",
      shortTitle: "Desk Worker Posture Blueprint",
      subtitle: "Ergonomics & Spine Health Guide for Work-From-Home & Office",
      shortDescription: "Eliminate hunchback posture, text-neck, and shoulder knots with micro-break stretches and optimal chair, desk, and monitor positioning guidelines.",
      longDescription: "Spending 8+ hours a day behind a screen takes a heavy toll on your spinal curvature. This comprehensive blueprint helps desk workers re-align rounded shoulders, activate weak upper back retractors, and eliminate slouching habits effortlessly.",
      categoryId: catPosture.id,
      priceINR: 149,
      comparePriceINR: 299,
      sku: "EBOOK-POSTURE-01",
      planName: "Instant Digital Download (Complete PDF Edition)",
      benefits: [
        "Anti-Slouch Alignment Reset Drills",
        "Workstation Height & Angle Ergonomic Formulas",
        "Printable 5-Minute Desk Stretch Wall Chart",
        "Instant PDF Delivery to Any Device",
      ],
      downloadUrl: "https://storage.convora.local/downloads/desk-worker-posture-guide.pdf",
      isFeatured: false,
      displayOrder: 4,
    },
    {
      title: "Deep Sleep & Anxiety Cortisol Reset Protocol",
      slug: "deep-sleep-cortisol-reset-guide",
      shortTitle: "Deep Sleep & Cortisol Reset Guide",
      subtitle: "Natural Circadian Rhythm Rebalancing & Nighttime Wind-Down Protocol",
      shortDescription: "Quiet mental chatter, lower nighttime cortisol spikes, and achieve deeper restorative REM sleep with clinically validated light, temperature, and wind-down routines.",
      longDescription: "Transform the quality of your sleep without habit-forming pills. Learn how to recalibrate your internal circadian clock, regulate melatonin production, and fall asleep within 15 minutes of head hitting the pillow.",
      categoryId: catSleep.id,
      priceINR: 179,
      comparePriceINR: 349,
      sku: "EBOOK-SLEEP-01",
      planName: "Instant Digital Download (Complete PDF Edition)",
      benefits: [
        "Nighttime Cortisol Spike Reduction Drills",
        "Optimal Light, Sound & Temperature Hygiene",
        "Pre-Sleep Mental De-Cluttering Protocol",
        "Instant PDF Download + Lifetime Access",
      ],
      downloadUrl: "https://storage.convora.local/downloads/deep-sleep-cortisol-reset-guide.pdf",
      isFeatured: false,
      displayOrder: 5,
    },
    {
      title: "Anti-Inflammatory Nutrition & Immunity Blueprint",
      slug: "anti-inflammatory-nutrition-guide",
      shortTitle: "Anti-Inflammatory Nutrition Guide",
      subtitle: "Practical 3-Week Food-as-Medicine Meal Plan & Anti-Bloat Guide",
      shortDescription: "Reduce systemic bodily inflammation, ease joint stiffness, and enhance daily energy levels using simple, locally available anti-inflammatory spices and whole foods.",
      longDescription: "Chronic low-grade inflammation is the root cause of persistent joint aches, fatigue, and digestive slowdowns. This practical guide introduces accessible, nutrient-dense meal plans designed to soothe bodily inflammation naturally.",
      categoryId: catGut.id,
      priceINR: 229,
      comparePriceINR: 499,
      sku: "EBOOK-INFLAMMATION-01",
      planName: "Instant Digital Download (Complete PDF Edition)",
      benefits: [
        "Inflammatory Trigger Foods to Avoid Guide",
        "Quick 20-Minute Anti-Inflammatory Recipes",
        "Weekly Grocery Shopping & Kitchen Prep List",
        "Instant Downloadable PDF Format",
      ],
      downloadUrl: "https://storage.convora.local/downloads/anti-inflammatory-nutrition-guide.pdf",
      isFeatured: false,
      displayOrder: 6,
    },
  ];

  for (const item of ebooks) {
    const product = await prisma.product.upsert({
      where: { slug: item.slug },
      update: {
        title: item.title,
        shortTitle: item.shortTitle,
        subtitle: item.subtitle,
        shortDescription: item.shortDescription,
        longDescription: item.longDescription,
        categoryId: item.categoryId,
        status: "PUBLISHED",
        visibility: "PUBLIC",
        isFeatured: item.isFeatured,
        displayOrder: item.displayOrder,
      },
      create: {
        title: item.title,
        slug: item.slug,
        shortTitle: item.shortTitle,
        subtitle: item.subtitle,
        shortDescription: item.shortDescription,
        longDescription: item.longDescription,
        categoryId: item.categoryId,
        status: "PUBLISHED",
        visibility: "PUBLIC",
        isFeatured: item.isFeatured,
        displayOrder: item.displayOrder,
        themePrimaryColor: "#00C8F4",
        themeAccentColor: "#1677FF",
        bonusEnabled: false,
      },
    });

    // Upsert Plan
    const plan = await prisma.plan.upsert({
      where: { sku: item.sku },
      update: {
        name: item.planName,
        duration: "Lifetime Access",
        price: item.priceINR * 100, // paise
        compareAtPrice: item.comparePriceINR * 100, // paise
        deliveryMethod: "DOWNLOAD",
        deliveryInstructions: "Click the download button below to save your digital eBook PDF immediately. A backup copy has also been sent to your email.",
        benefits: JSON.stringify(item.benefits),
        stockPolicy: "UNLIMITED",
        isActive: true,
        isDefault: true,
        isRecommended: true,
      },
      create: {
        productId: product.id,
        name: item.planName,
        sku: item.sku,
        duration: "Lifetime Access",
        price: item.priceINR * 100,
        compareAtPrice: item.comparePriceINR * 100,
        deliveryMethod: "DOWNLOAD",
        deliveryInstructions: "Click the download button below to save your digital eBook PDF immediately. A backup copy has also been sent to your email.",
        benefits: JSON.stringify(item.benefits),
        stockPolicy: "UNLIMITED",
        isActive: true,
        isDefault: true,
        isRecommended: true,
      },
    });

    // Add an available inventory item for instant fulfillment
    const invCount = await prisma.credentialInventory.count({
      where: { planId: plan.id, status: "AVAILABLE" },
    });

    if (invCount === 0) {
      const payload = JSON.stringify({
        downloadUrl: item.downloadUrl,
        notes: `Thank you for your purchase! Download your digital eBook copy of '${item.title}' using the secure link above. Save to your device for offline reading.`,
      });
      const { encryptedData, iv, authTag } = encryptCredential(payload);

      await prisma.credentialInventory.create({
        data: {
          planId: plan.id,
          status: "AVAILABLE",
          plainHint: "Digital PDF eBook Access",
          encryptedData,
          iv,
          authTag,
          setupInstructions: "Open the PDF file on any smartphone, iPad/tablet, or computer using Adobe Acrobat, Apple Books, or Google Drive.",
        },
      });
    }

    console.log(`✓ Seeded eBook: ${item.title} (₹${item.priceINR})`);
  }

  // Also update store_name and store_tagline to match eBook / digital education profile
  await prisma.storeSetting.upsert({
    where: { key: "store_tagline" },
    update: { value: "Evidence-Based Digital Guides & Educational eBooks" },
    create: { key: "store_tagline", value: "Evidence-Based Digital Guides & Educational eBooks", group: "STORE" },
  });

  console.log("All eBooks seeded successfully!");
}

seedEbooks()
  .catch((e) => {
    console.error("Error seeding eBooks:", e);
    process.exit(1);
  })
  .finally(() => prisma.$disconnect());

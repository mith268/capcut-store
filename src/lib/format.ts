/**
 * Format an integer paise value into Indian Rupee string (e.g. 29900 -> "₹299")
 */
export function formatINR(paise: number, showDecimals = false): string {
  const rupees = paise / 100;
  if (showDecimals || rupees % 1 !== 0) {
    return new Intl.NumberFormat("en-IN", {
      style: "currency",
      currency: "INR",
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(rupees);
  }

  return new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR",
    maximumFractionDigits: 0,
  }).format(rupees);
}

/**
 * Calculate savings percentage between compare-at price and selling price
 */
export function calculateSavingsPercentage(compareAtPaise: number, salePaise: number): number {
  if (!compareAtPaise || compareAtPaise <= salePaise) return 0;
  return Math.round(((compareAtPaise - salePaise) / compareAtPaise) * 100);
}

/**
 * Safe JSON parser with fallback default
 */
export function safeJsonParse<T>(jsonString: string | null | undefined, fallback: T): T {
  if (!jsonString) return fallback;
  try {
    return JSON.parse(jsonString) as T;
  } catch {
    return fallback;
  }
}

/**
 * Format date nicely
 */
export function formatDate(date: Date | string | null | undefined): string {
  if (!date) return "—";
  const d = new Date(date);
  return d.toLocaleDateString("en-IN", {
    day: "numeric",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

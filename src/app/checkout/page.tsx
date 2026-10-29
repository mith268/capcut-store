"use client";

import { useEffect, useState, Suspense } from "react";
import { useSearchParams, useRouter } from "next/navigation";
import Link from "next/link";
import { formatINR } from "@/lib/format";
import {
  Lock,
  ShieldCheck,
  Zap,
  CheckCircle2,
  Tag,
  AlertCircle,
  ArrowLeft,
  Loader2,
  Sparkles,
} from "lucide-react";

declare global {
  interface Window {
    Razorpay: any;
  }
}

interface CalculateResponse {
  success: boolean;
  unitPrice: number;
  quantity: number;
  subtotal: number;
  discountAmount: number;
  totalAmount: number;
  couponMessage: string | null;
  coupon: { id: string; code: string; discountAmount: number } | null;
  plan: {
    id: string;
    name: string;
    duration: string;
    sku: string;
    deliveryMethod: string;
  };
  product: {
    id: string;
    title: string;
    slug: string;
  };
}

function CheckoutForm() {
  const searchParams = useSearchParams();
  const router = useRouter();

  const productIdParam = searchParams.get("product") || "";
  const planIdParam = searchParams.get("plan") || "";

  // Form State
  const [customerName, setCustomerName] = useState("");
  const [customerEmail, setCustomerEmail] = useState("");
  const [customerPhone, setCustomerPhone] = useState("");
  const [couponCode, setCouponCode] = useState("");
  const [couponInput, setCouponInput] = useState("");

  // Order calculation state
  const [loading, setLoading] = useState(true);
  const [calculating, setCalculating] = useState(false);
  const [paying, setPaying] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [orderSummary, setOrderSummary] = useState<CalculateResponse | null>(null);

  // Initial load: fetch product and plan if not provided, or calculate totals
  useEffect(() => {
    async function loadInitialCheckout() {
      try {
        setLoading(true);
        setError(null);

        // If product/plan not in query, fetch first published product & plan
        let resolvedProductId = productIdParam;
        let resolvedPlanId = planIdParam;

        if (!resolvedProductId || !resolvedPlanId) {
          const res = await fetch("/api/checkout/default-product");
          const data = await res.json();
          if (data.product && data.plan) {
            resolvedProductId = data.product.id;
            resolvedPlanId = data.plan.id;
          } else {
            setError("No active products available for purchase.");
            setLoading(false);
            return;
          }
        }

        const calcRes = await fetch("/api/checkout/calculate", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            productId: resolvedProductId,
            planId: resolvedPlanId,
            couponCode: couponCode || undefined,
          }),
        });

        const calcData = await calcRes.json();
        if (!calcRes.ok) {
          setError(calcData.error || "Unable to load plan details.");
        } else {
          setOrderSummary(calcData);
        }
      } catch {
        setError("Failed to load checkout. Please refresh or try again.");
      } finally {
        setLoading(false);
      }
    }

    loadInitialCheckout();
  }, [productIdParam, planIdParam, couponCode]);

  // Apply Coupon Handler
  async function handleApplyCoupon(e: React.FormEvent) {
    e.preventDefault();
    if (!couponInput.trim()) return;

    try {
      setCalculating(true);
      setError(null);

      const res = await fetch("/api/checkout/calculate", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          productId: orderSummary?.product.id,
          planId: orderSummary?.plan.id,
          couponCode: couponInput.trim(),
        }),
      });

      const data = await res.json();
      if (!res.ok) {
        setError(data.error || "Failed to apply coupon.");
      } else {
        setOrderSummary(data);
        setCouponCode(couponInput.trim());
      }
    } catch {
      setError("Error checking coupon code.");
    } finally {
      setCalculating(false);
    }
  }

  // Payment Initiation Handler
  async function handlePayment(e: React.FormEvent) {
    e.preventDefault();
    setError(null);

    if (!customerEmail || !customerEmail.includes("@")) {
      setError("Please provide a valid email address to receive your digital credentials.");
      return;
    }

    if (!orderSummary) return;

    try {
      setPaying(true);

      // 1. Create order on server and get Razorpay order ID
      const initiateRes = await fetch("/api/checkout/initiate", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          productId: orderSummary.product.id,
          planId: orderSummary.plan.id,
          customerName: customerName || "Customer",
          customerEmail,
          customerPhone,
          couponCode: orderSummary.coupon?.code || undefined,
          utmSource: searchParams.get("utm_source") || undefined,
          utmMedium: searchParams.get("utm_medium") || undefined,
          utmCampaign: searchParams.get("utm_campaign") || undefined,
          utmContent: searchParams.get("utm_content") || undefined,
          utmTerm: searchParams.get("utm_term") || undefined,
        }),
      });

      const initiateData = await initiateRes.json();

      if (!initiateRes.ok) {
        setError(initiateData.error || "Failed to create payment order.");
        setPaying(false);
        return;
      }

      const { order, razorpay, accessToken } = initiateData;

      // Handle Mock Mode (when using placeholder Razorpay test keys)
      if (razorpay.isMock || !window.Razorpay) {
        // Direct test verification for development and local testing
        const verifyRes = await fetch("/api/checkout/verify", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            publicId: order.publicId,
            razorpay_order_id: razorpay.orderId,
            razorpay_payment_id: `pay_mock_${Date.now()}`,
            razorpay_signature: "mock_signature_valid",
            accessToken,
          }),
        });

        const verifyData = await verifyRes.json();
        if (verifyData.success && verifyData.redirectUrl) {
          router.push(verifyData.redirectUrl);
          return;
        } else {
          setError(verifyData.error || "Payment verification failed.");
          setPaying(false);
          return;
        }
      }

      // Live / Real Razorpay Test Mode modal
      const options = {
        key: razorpay.keyId,
        amount: razorpay.amount,
        currency: razorpay.currency,
        name: "Convora Store",
        description: `${orderSummary.product.title} - ${orderSummary.plan.name}`,
        order_id: razorpay.orderId,
        prefill: {
          name: customerName,
          email: customerEmail,
          contact: customerPhone,
        },
        theme: {
          color: "#2563EB",
        },
        handler: async function (response: any) {
          try {
            const verifyRes = await fetch("/api/checkout/verify", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                publicId: order.publicId,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_signature: response.razorpay_signature,
                accessToken,
              }),
            });

            const verifyData = await verifyRes.json();
            if (verifyData.success && verifyData.redirectUrl) {
              router.push(verifyData.redirectUrl);
            } else {
              setError(verifyData.error || "Payment verification failed.");
              setPaying(false);
            }
          } catch {
            setError("Error verifying payment response.");
            setPaying(false);
          }
        },
        modal: {
          ondismiss: function () {
            setPaying(false);
          },
        },
      };

      const rzp = new window.Razorpay(options);
      rzp.on("payment.failed", function (resp: any) {
        setError(resp.error?.description || "Payment was declined by bank.");
        setPaying(false);
      });
      rzp.open();
    } catch (err) {
      console.error(err);
      setError("An unexpected error occurred during checkout.");
      setPaying(false);
    }
  }

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center p-4 bg-[#F8FAFC]">
        <div className="flex items-center space-x-3 text-brand-blue">
          <Loader2 className="w-6 h-6 animate-spin" />
          <span className="font-bold text-sm text-[#0F172A]">Preparing secure checkout...</span>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen py-10 px-4 sm:px-6 lg:px-8 bg-[#F8FAFC]">
      <div className="max-w-4xl mx-auto space-y-8">
        
        {/* Top Back Link & Security Badge */}
        <div className="flex items-center justify-between">
          <Link
            href={orderSummary ? `/products/${orderSummary.product.slug}` : "/"}
            className="flex items-center space-x-1.5 text-xs text-[#64748B] hover:text-[#0F172A] transition-colors font-medium"
          >
            <ArrowLeft className="w-3.5 h-3.5" />
            <span>Back to offer</span>
          </Link>

          <div className="flex items-center space-x-1.5 text-xs text-emerald-700 font-semibold bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
            <Lock className="w-3.5 h-3.5" />
            <span>256-Bit Bank Encrypted Checkout</span>
          </div>
        </div>

        {/* Error Alert */}
        {error && (
          <div className="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start space-x-3 shadow-soft-sm">
            <AlertCircle className="w-5 h-5 shrink-0 mt-0.5 text-rose-600" />
            <div>
              <strong className="block font-bold">Action Required</strong>
              <span>{error}</span>
            </div>
          </div>
        )}

        {/* 2-Column Checkout Layout */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          
          {/* Left Column: Customer Form (7 cols) */}
          <div className="lg:col-span-7 space-y-6">
            <div className="p-6 sm:p-8 rounded-3xl bg-white border border-[#E2E8F0] shadow-soft-md space-y-6">
              
              <div>
                <h1 className="text-xl sm:text-2xl font-black text-[#0F172A]">
                  Customer & Delivery Details
                </h1>
                <p className="text-xs text-[#64748B] mt-1">
                  Your digital credentials, download links, and receipt will be delivered instantly to this email.
                </p>
              </div>

              <form onSubmit={handlePayment} className="space-y-4">
                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-[#475569] mb-1.5">
                    Delivery Email Address <span className="text-rose-500">*</span>
                  </label>
                  <input
                    type="email"
                    required
                    value={customerEmail}
                    onChange={(e) => setCustomerEmail(e.target.value)}
                    placeholder="you@gmail.com"
                    className="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] focus:border-brand-blue focus:bg-white px-4 py-3 text-sm text-[#0F172A] outline-none transition-colors shadow-soft-sm"
                  />
                  <span className="text-[11px] text-[#64748B] mt-1 block">
                    Important: Ensure your email is correct. Access credentials are dispatched immediately.
                  </span>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs font-bold uppercase tracking-wider text-[#475569] mb-1.5">
                      Your Full Name
                    </label>
                    <input
                      type="text"
                      value={customerName}
                      onChange={(e) => setCustomerName(e.target.value)}
                      placeholder="Alex Sharma"
                      className="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] focus:border-brand-blue focus:bg-white px-4 py-3 text-sm text-[#0F172A] outline-none transition-colors shadow-soft-sm"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-bold uppercase tracking-wider text-[#475569] mb-1.5">
                      WhatsApp Number (Optional)
                    </label>
                    <input
                      type="tel"
                      value={customerPhone}
                      onChange={(e) => setCustomerPhone(e.target.value)}
                      placeholder="9876543210"
                      className="w-full rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] focus:border-brand-blue focus:bg-white px-4 py-3 text-sm text-[#0F172A] outline-none transition-colors shadow-soft-sm"
                    />
                  </div>
                </div>

                {/* Delivery Guarantee Notice */}
                <div className="p-4 rounded-xl bg-blue-50/70 border border-blue-100 flex items-start space-x-3 text-xs text-[#1E40AF]">
                  <ShieldCheck className="w-5 h-5 text-brand-blue shrink-0 mt-0.5" />
                  <div>
                    <strong className="text-[#0F172A] block font-bold">Automatic Instant Fulfillment</strong>
                    <span className="text-[#475569]">No waiting. As soon as payment succeeds, your access credentials and setup instructions appear directly on screen.</span>
                  </div>
                </div>

                {/* Submit Payment CTA */}
                <button
                  type="submit"
                  disabled={paying}
                  className="w-full py-4 rounded-xl btn-primary-blue text-sm font-bold tracking-wide uppercase flex items-center justify-center space-x-2 disabled:opacity-50 text-white shadow-soft-md"
                >
                  {paying ? (
                    <>
                      <Loader2 className="w-4 h-4 animate-spin" />
                      <span>Processing Payment...</span>
                    </>
                  ) : (
                    <>
                      <Zap className="w-4 h-4" />
                      <span>
                        Pay {orderSummary ? formatINR(orderSummary.totalAmount) : ""} via Razorpay
                      </span>
                    </>
                  )}
                </button>

                <div className="text-center text-[11px] text-[#64748B]">
                  Accepts UPI (GPay, PhonePe, Paytm, CRED), Debit/Credit Cards, NetBanking & Wallets.
                </div>
              </form>

            </div>
          </div>

          {/* Right Column: Order Summary & Coupon (5 cols) */}
          <div className="lg:col-span-5 space-y-6">
            <div className="p-6 sm:p-8 rounded-3xl bg-white border border-[#E2E8F0] shadow-soft-md space-y-6">
              
              <div className="border-b border-[#E2E8F0] pb-4">
                <h2 className="text-base font-bold text-[#0F172A]">Order Summary</h2>
                <div className="text-xs text-brand-blue font-mono font-semibold mt-0.5">
                  100% Genuine Digital License
                </div>
              </div>

              {orderSummary && (
                <div className="space-y-4 text-xs">
                  {/* Product & Plan Details */}
                  <div className="p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-2">
                    <div className="font-black text-sm text-[#0F172A]">
                      {orderSummary.product.title}
                    </div>
                    <div className="flex items-center justify-between text-[#64748B] font-mono">
                      <span>Plan: {orderSummary.plan.name}</span>
                      <span className="text-[#0F172A] font-bold">{formatINR(orderSummary.unitPrice)}</span>
                    </div>
                    <div className="flex items-center space-x-1.5 text-emerald-700 font-bold text-[11px] pt-1.5 border-t border-[#E2E8F0]">
                      <Zap className="w-3.5 h-3.5" />
                      <span>Instant Digital Delivery</span>
                    </div>
                  </div>

                  {/* Coupon Form */}
                  <form onSubmit={handleApplyCoupon} className="flex gap-2">
                    <input
                      type="text"
                      value={couponInput}
                      onChange={(e) => setCouponInput(e.target.value.toUpperCase())}
                      placeholder="Coupon Code (e.g. SAVE50)"
                      className="flex-1 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] px-3 py-2 text-xs text-[#0F172A] uppercase outline-none focus:border-brand-blue font-mono shadow-soft-sm"
                    />
                    <button
                      type="submit"
                      disabled={calculating}
                      className="px-4 py-2 rounded-xl bg-white border border-[#E2E8F0] hover:border-brand-blue text-[#0F172A] font-bold text-xs shrink-0 transition-colors shadow-soft-sm"
                    >
                      {calculating ? "..." : "Apply"}
                    </button>
                  </form>

                  {orderSummary.couponMessage && (
                    <div
                      className={`text-[11px] font-bold ${
                        orderSummary.coupon ? "text-emerald-600" : "text-amber-600"
                      }`}
                    >
                      {orderSummary.couponMessage}
                    </div>
                  )}

                  {/* Price Calculations */}
                  <div className="space-y-2 pt-4 border-t border-[#E2E8F0] text-xs">
                    <div className="flex justify-between text-[#64748B]">
                      <span>Subtotal</span>
                      <span className="font-mono text-[#0F172A] font-medium">{formatINR(orderSummary.subtotal)}</span>
                    </div>

                    {orderSummary.discountAmount > 0 && (
                      <div className="flex justify-between text-emerald-600 font-bold">
                        <span>Discount ({orderSummary.coupon?.code})</span>
                        <span className="font-mono">-{formatINR(orderSummary.discountAmount)}</span>
                      </div>
                    )}

                    <div className="flex justify-between text-base font-black text-[#0F172A] pt-2 border-t border-[#E2E8F0]">
                      <span>Total Payable</span>
                      <span className="font-mono text-brand-blue">
                        {formatINR(orderSummary.totalAmount)}
                      </span>
                    </div>
                  </div>

                </div>
              )}

              {/* Guarantees Box */}
              <div className="p-4 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] space-y-2 text-[11px] text-[#64748B]">
                <div className="flex items-center space-x-1.5 text-[#0F172A] font-bold">
                  <CheckCircle2 className="w-3.5 h-3.5 text-brand-blue" />
                  <span>30-Day Replacement Warranty Included</span>
                </div>
                <p className="leading-relaxed">
                  If you face any issues, our 24x7 support team replaces your credentials immediately via WhatsApp.
                </p>
              </div>

            </div>
          </div>

        </div>

      </div>
    </div>
  );
}

export default function CheckoutPage() {
  return (
    <Suspense
      fallback={
        <div className="min-h-screen bg-[#F8FAFC] flex items-center justify-center text-[#64748B] text-xs">
          <Loader2 className="w-6 h-6 animate-spin text-brand-blue mr-2" />
          Loading secure checkout...
        </div>
      }
    >
      <CheckoutForm />
    </Suspense>
  );
}

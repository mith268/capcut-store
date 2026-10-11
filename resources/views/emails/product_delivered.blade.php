<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $storeSettings['store_name'] ?? 'CapCut Pro' }} Access Delivered</title>
</head>
<body style="background-color: #0b0f19; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #f1f5f9; padding: 24px 0; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #111827; border: 1px solid #1e293b; border-radius: 12px; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #00d9ff, #1677ff); padding: 24px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">{{ $storeSettings['store_name'] ?? 'CapCut Pro' }} Access Ready</h1>
            <p style="color: #e0f2fe; margin: 6px 0 0; font-size: 14px;">Order #{{ $order->order_number }}</p>
        </div>
        
        <div style="padding: 28px;">
            <p style="font-size: 16px; line-height: 1.5; color: #f8fafc;">
                Hello <strong>{{ $order->customer_name }}</strong>,
            </p>
            <p style="font-size: 14px; line-height: 1.6; color: #94a3b8;">
                Your payment for <strong>{{ $order->product->name }}</strong> has been verified. Your 1-Month access with 1200 Credits is provisioned and ready in your private customer portal.
            </p>

            <div style="margin: 28px 0; text-align: center;">
                <a href="{{ $order->getSecureUrl() }}" style="background: linear-gradient(135deg, #00d9ff, #1677ff); color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: 700; font-size: 15px; display: inline-block; box-shadow: 0 4px 14px rgba(0, 217, 255, 0.4);">
                    👉 View My Credentials & Login Details
                </a>
            </div>

            <div style="background-color: #1e293b; border-radius: 8px; padding: 16px; margin-top: 24px;">
                <h3 style="margin: 0 0 8px; font-size: 14px; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.5px;">⚠️ Device Rules & 30-Day Warranty</h3>
                <ul style="margin: 0; padding-left: 18px; font-size: 13px; color: #cbd5e1; line-height: 1.6;">
                    <li><strong>Device Limit:</strong> Maximum 1 mobile device + 1 laptop/PC.</li>
                    <li><strong>Warranty:</strong> Covered for 30 days under normal use.</li>
                    <li><strong>Email Protection:</strong> Do NOT change the account email.</li>
                </ul>
            </div>

            <p style="font-size: 13px; color: #64748b; margin-top: 24px; line-height: 1.5;">
                Need help? Contact our WhatsApp support with your Order ID #{{ $order->order_number }}.
            </p>
        </div>

        <div style="background-color: #0b0f19; padding: 16px 28px; border-top: 1px solid #1e293b; font-size: 12px; color: #64748b; text-align: center;">
            Independent digital service provider. Not affiliated with or endorsed by CapCut/ByteDance unless explicitly stated otherwise.
        </div>
    </div>
</body>
</html>

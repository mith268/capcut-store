<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Notification</title>
</head>
<body style="background-color: #0b0f19; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #f1f5f9; padding: 24px 0; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #111827; border: 1px solid #1e293b; border-radius: 12px; padding: 24px;">
        <h2 style="color: #ff6b6b; margin-top: 0;">Payment Incomplete</h2>
        <p>Hello {{ $order->customer_name }},</p>
        <p style="color: #94a3b8;">We could not complete your payment for order <strong>#{{ $order->order_number }}</strong>.</p>
        @if(!empty($reason))
            <p style="background: #2d1515; border-left: 4px solid #ff6b6b; padding: 10px 14px; color: #fca5a5; font-size: 14px;">Reason: {{ $reason }}</p>
        @endif
        <p style="color: #94a3b8;">You may retry payment or complete your purchase securely via WhatsApp.</p>
        <p style="text-align: center; margin: 24px 0;">
            <a href="{{ route('checkout.show', ['order_id' => $order->id]) }}" style="background: #1677ff; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;">
                Retry Payment
            </a>
        </p>
    </div>
</body>
</html>

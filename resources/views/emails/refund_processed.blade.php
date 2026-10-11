<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Refund Processed</title>
</head>
<body style="background-color: #0b0f19; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #f1f5f9; padding: 24px 0; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #111827; border: 1px solid #1e293b; border-radius: 12px; padding: 24px;">
        <h2 style="color: #38bdf8; margin-top: 0;">Refund Processed</h2>
        <p>Hello {{ $order->customer_name }},</p>
        <p style="color: #94a3b8;">A refund of <strong>₹{{ number_format($order->total_amount, 2) }}</strong> has been initiated for your order <strong>#{{ $order->order_number }}</strong>.</p>
        @if(!empty($note))
            <p style="background: #1e293b; padding: 12px; border-radius: 6px; font-size: 14px; color: #cbd5e1;">Note: {{ $note }}</p>
        @endif
        <p style="color: #94a3b8;">Refunds typically reflect in your original payment method (Bank/UPI/Card) within 5–7 business days per banking settlement cycles.</p>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Received</title>
</head>
<body style="background-color: #0b0f19; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #f1f5f9; padding: 24px 0; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #111827; border: 1px solid #1e293b; border-radius: 12px; overflow: hidden;">
        <div style="background: #1e293b; padding: 20px; text-align: center;">
            <h2 style="color: #ffffff; margin: 0; font-size: 20px;">Order Received: #{{ $order->order_number }}</h2>
        </div>
        <div style="padding: 24px;">
            <p>Hello {{ $order->customer_name }},</p>
            <p style="color: #94a3b8;">We have received your order for <strong>{{ $order->product->name }}</strong>. Total amount: <strong>₹{{ number_format($order->total_amount, 2) }}</strong>.</p>
            <p style="color: #94a3b8;">If you have completed your payment, your order will be verified automatically. You can track status using your secure order link below:</p>
            <p style="text-align: center; margin: 24px 0;">
                <a href="{{ $order->getSecureUrl() }}" style="background: #1677ff; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;">
                    View Order Details
                </a>
            </p>
        </div>
    </div>
</body>
</html>

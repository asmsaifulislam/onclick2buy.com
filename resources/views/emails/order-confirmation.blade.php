<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f4f7fa; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 32px; text-align: center; }
        .header h1 { color: #fff; font-size: 22px; margin: 0; }
        .header p { color: rgba(255,255,255,0.8); font-size: 13px; margin: 4px 0 0; }
        .body { padding: 32px; }
        .greeting { font-size: 16px; margin-bottom: 20px; }
        .order-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin: 20px 0; }
        .order-title { font-size: 14px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px; }
        .order-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e2e8f0; }
        .order-row:last-child { border-bottom: none; }
        .order-label { color: #64748b; font-size: 14px; }
        .order-value { font-weight: 600; font-size: 14px; color: #1e293b; }
        .total-row { background: #4f46e5; color: #fff; margin: 20px -24px -24px; padding: 16px 24px; border-radius: 0 0 12px 12px; display: flex; justify-content: space-between; }
        .footer { padding: 24px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; color: #94a3b8; font-size: 12px; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; margin: 16px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>OnClick2Buy</h1>
            <p>Order Confirmation</p>
        </div>
        <div class="body">
            <p class="greeting">Dear {{ $order->customer_name }},</p>
            <p style="font-size:14px; line-height:1.6; color:#475569;">Thank you for your order! Here are your order details:</p>

            <div class="order-box">
                <div class="order-title">Order Details</div>
                <div class="order-row">
                    <span class="order-label">Product</span>
                    <span class="order-value">{{ $order->product_name }}</span>
                </div>
                <div class="order-row">
                    <span class="order-label">Quantity</span>
                    <span class="order-value">{{ $order->quantity }}</span>
                </div>
                <div class="order-row">
                    <span class="order-label">Unit Price</span>
                    <span class="order-value">${{ number_format($order->unit_price, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="order-label" style="color:#fff;">Total Amount</span>
                    <span class="order-value" style="color:#fff;">${{ number_format($order->total_price, 2) }}</span>
                </div>
            </div>

            <div class="order-box">
                <div class="order-title">Shipping Information</div>
                <div class="order-row">
                    <span class="order-label">Name</span>
                    <span class="order-value">{{ $order->customer_name }}</span>
                </div>
                <div class="order-row">
                    <span class="order-label">Email</span>
                    <span class="order-value">{{ $order->email }}</span>
                </div>
                @if($order->phone)
                <div class="order-row">
                    <span class="order-label">Phone</span>
                    <span class="order-value">{{ $order->phone }}</span>
                </div>
                @endif
                @if($order->shipping_address)
                <div class="order-row">
                    <span class="order-label">Address</span>
                    <span class="order-value">{{ $order->shipping_address }}</span>
                </div>
                @endif
                <div class="order-row">
                    <span class="order-label">Status</span>
                    <span class="order-value" style="color:#4f46e5;">{{ ucfirst($order->status) }}</span>
                </div>
            </div>

            <p style="font-size:14px; line-height:1.6; color:#475569;">If you have any questions, please don't hesitate to contact us.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} OnClick2Buy. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

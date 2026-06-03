<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order - {{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f1f1;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f1f1;padding:30px 0;">
    <tr>
        <td align="center">
            <table width="620" cellpadding="0" cellspacing="0" border="0" style="max-width:620px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

                {{-- ===== HEADER ===== --}}
                <tr>
                    <td style="background-color:#CC0000;padding:28px 36px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td>
                  <span style="font-size:22px;font-weight:900;color:#ffffff;letter-spacing:1px;">
                    P<span style="color:#ffdd00;">|</span>PIXEL PRESS
                  </span>
                                    <br>
                                    <span style="font-size:12px;color:#ffcccc;letter-spacing:2px;text-transform:uppercase;">DTF Transfers Chicago</span>
                                </td>
                                <td align="right">
                  <span style="background:#ffffff;color:#CC0000;font-size:11px;font-weight:bold;padding:6px 14px;border-radius:20px;letter-spacing:1px;text-transform:uppercase;">
                    🛒 New Order
                  </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- ===== HERO BANNER ===== --}}
                <tr>
                    <td style="background:#1a0000;padding:20px 36px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td>
                                    <p style="margin:0;font-size:20px;font-weight:bold;color:#ffffff;">
                                        New Order Received
                                    </p>
                                    <p style="margin:4px 0 0;font-size:13px;color:#aaa;">
                                        Order <strong style="color:#ff6666;">{{ $order->order_number }}</strong>
                                        &nbsp;&bull;&nbsp; {{ $order->created_at->format('F j, Y \a\t g:i A') }}
                                    </p>
                                </td>
                                <td align="right">
                  <span style="background:#CC0000;color:#fff;font-size:13px;font-weight:bold;padding:8px 18px;border-radius:4px;">
                    {{ $order->status->label() }}
                  </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- ===== BODY ===== --}}
                <tr>
                    <td style="padding:28px 36px;">

                        {{-- ORDER DETAILS --}}
                        <p style="margin:0 0 10px;font-size:11px;font-weight:bold;color:#CC0000;letter-spacing:2px;text-transform:uppercase;border-bottom:2px solid #CC0000;padding-bottom:6px;">Order Details</p>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e8e8e8;border-radius:4px;overflow:hidden;margin-bottom:24px;">
                            <tr>
                                <td width="40%" style="padding:10px 14px;background:#fafafa;font-size:13px;font-weight:bold;color:#555;border-bottom:1px solid #eee;">Order Number</td>
                                <td style="padding:10px 14px;font-size:13px;color:#222;border-bottom:1px solid #eee;font-weight:bold;">{{ $order->order_number }}</td>
                            </tr>
                            <tr>
                                <td width="40%" style="padding:10px 14px;background:#fafafa;font-size:13px;font-weight:bold;color:#555;border-bottom:1px solid #eee;">Payment Method</td>
                                <td style="padding:10px 14px;font-size:13px;color:#222;border-bottom:1px solid #eee;">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                            </tr>
                            <tr>
                                <td width="40%" style="padding:10px 14px;background:#fafafa;font-size:13px;font-weight:bold;color:#555;border-bottom:1px solid #eee;">Shipping Method</td>
                                <td style="padding:10px 14px;font-size:13px;color:#222;border-bottom:1px solid #eee;">{{ ucfirst(str_replace('_', ' ', $order->shipping_method)) }}</td>
                            </tr>
                            <tr>
                                <td width="40%" style="padding:10px 14px;background:#fafafa;font-size:13px;font-weight:bold;color:#555;">Placed At</td>
                                <td style="padding:10px 14px;font-size:13px;color:#222;">{{ $order->created_at->format('M d, Y — g:i A') }}</td>
                            </tr>
                        </table>

                        {{-- CUSTOMER --}}
                        <p style="margin:0 0 10px;font-size:11px;font-weight:bold;color:#CC0000;letter-spacing:2px;text-transform:uppercase;border-bottom:2px solid #CC0000;padding-bottom:6px;">Customer</p>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e8e8e8;border-radius:4px;overflow:hidden;margin-bottom:24px;">
                            <tr>
                                <td width="40%" style="padding:10px 14px;background:#fafafa;font-size:13px;font-weight:bold;color:#555;border-bottom:1px solid #eee;">Name</td>
                                <td style="padding:10px 14px;font-size:13px;color:#222;border-bottom:1px solid #eee;">{{ $order->user->name }}</td>
                            </tr>
                            <tr>
                                <td width="40%" style="padding:10px 14px;background:#fafafa;font-size:13px;font-weight:bold;color:#555;border-bottom:1px solid #eee;">Email</td>
                                <td style="padding:10px 14px;font-size:13px;color:#222;border-bottom:1px solid #eee;">{{ $order->user->email }}</td>
                            </tr>
                            @if($order->user->phone ?? null)
                                <tr>
                                    <td width="40%" style="padding:10px 14px;background:#fafafa;font-size:13px;font-weight:bold;color:#555;">Phone</td>
                                    <td style="padding:10px 14px;font-size:13px;color:#222;">{{ $order->user->phone }}</td>
                                </tr>
                            @endif
                        </table>

                        {{-- ORDER ITEMS --}}
                        <p style="margin:0 0 10px;font-size:11px;font-weight:bold;color:#CC0000;letter-spacing:2px;text-transform:uppercase;border-bottom:2px solid #CC0000;padding-bottom:6px;">Order Items ({{ $order->items->count() }})</p>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e8e8e8;border-radius:4px;overflow:hidden;margin-bottom:24px;">
                            <tr style="background:#CC0000;">
                                <td style="padding:10px 14px;font-size:12px;font-weight:bold;color:#fff;text-transform:uppercase;letter-spacing:1px;">Product</td>
                                <td style="padding:10px 14px;font-size:12px;font-weight:bold;color:#fff;text-transform:uppercase;letter-spacing:1px;text-align:center;">Qty</td>
                                <td style="padding:10px 14px;font-size:12px;font-weight:bold;color:#fff;text-transform:uppercase;letter-spacing:1px;text-align:right;">Unit Price</td>
                                <td style="padding:10px 14px;font-size:12px;font-weight:bold;color:#fff;text-transform:uppercase;letter-spacing:1px;text-align:right;">Total</td>
                            </tr>
                            @foreach($order->items as $index => $item)
                                <tr style="background:{{ $index % 2 === 0 ? '#ffffff' : '#fafafa' }}">
                                    <td style="padding:10px 14px;font-size:13px;color:#222;border-top:1px solid #eee;">{{ $item->product->name ?? 'Product' }}</td>
                                    <td style="padding:10px 14px;font-size:13px;color:#222;border-top:1px solid #eee;text-align:center;">{{ $item->quantity }}</td>
                                    <td style="padding:10px 14px;font-size:13px;color:#222;border-top:1px solid #eee;text-align:right;">${{ number_format($item->unit_price, 2) }}</td>
                                    <td style="padding:10px 14px;font-size:13px;color:#222;border-top:1px solid #eee;text-align:right;font-weight:bold;">${{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </table>

                        {{-- FINANCIALS --}}
                        <p style="margin:0 0 10px;font-size:11px;font-weight:bold;color:#CC0000;letter-spacing:2px;text-transform:uppercase;border-bottom:2px solid #CC0000;padding-bottom:6px;">Order Summary</p>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e8e8e8;border-radius:4px;overflow:hidden;margin-bottom:28px;">
                            <tr>
                                <td style="padding:10px 14px;background:#fafafa;font-size:13px;color:#555;border-bottom:1px solid #eee;">Subtotal</td>
                                <td style="padding:10px 14px;font-size:13px;color:#222;border-bottom:1px solid #eee;text-align:right;">{{ $order->formatted_subtotal }}</td>
                            </tr>
                            @if($order->discount_amount > 0)
                                <tr>
                                    <td style="padding:10px 14px;background:#fafafa;font-size:13px;color:#555;border-bottom:1px solid #eee;">Discount</td>
                                    <td style="padding:10px 14px;font-size:13px;color:#c0392b;border-bottom:1px solid #eee;text-align:right;font-weight:bold;">-${{ number_format($order->discount_amount, 2) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td style="padding:10px 14px;background:#fafafa;font-size:13px;color:#555;border-bottom:1px solid #eee;">Tax</td>
                                <td style="padding:10px 14px;font-size:13px;color:#222;border-bottom:1px solid #eee;text-align:right;">${{ number_format($order->tax_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px 14px;background:#fafafa;font-size:13px;color:#555;border-bottom:1px solid #eee;">Shipping</td>
                                <td style="padding:10px 14px;font-size:13px;color:#222;border-bottom:1px solid #eee;text-align:right;">${{ number_format($order->shipping_amount, 2) }}</td>
                            </tr>
                            <tr style="background:#CC0000;">
                                <td style="padding:12px 14px;font-size:14px;font-weight:bold;color:#ffffff;">Total</td>
                                <td style="padding:12px 14px;font-size:16px;font-weight:bold;color:#ffffff;text-align:right;">{{ $order->formatted_total }}</td>
                            </tr>
                        </table>

                        {{-- CTA BUTTON --}}
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center">
                                    <a href="https://pixelpressdtf.com"
                                       style="display:inline-block;background:#CC0000;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:14px 36px;border-radius:4px;letter-spacing:0.5px;">
                                        View Order in Admin Panel →
                                    </a>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                {{-- ===== FOOTER ===== --}}
                <tr>
                    <td style="background:#1a0000;padding:18px 36px;text-align:center;">
                        <p style="margin:0;font-size:12px;color:#888;">
                            This is an automated notification from
                            <strong style="color:#ff6666;">Pixel Press DTF</strong>.
                            Do not reply to this email.
                        </p>
                        <p style="margin:6px 0 0;font-size:11px;color:#666;">
                            Chicago's #1 DTF Transfer Service — Same Day Service Available
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>

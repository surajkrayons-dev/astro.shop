<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
</head>

<body style="margin:0;background:#fff3e0;font-family:Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:10px;">
        <tr>
            <td align="center">

                <table width="100%" style="max-width:600px;background:#ffffff;border-radius:14px;overflow:hidden;">

                    <!-- HEADER -->
                    <tr>
                        <td
                            style="background:linear-gradient(135deg,#c62828,#ef5350);padding:20px;text-align:center;color:#fff;">
                            <h2 style="margin:0;">❌ Order Cancelled</h2>
                            <p style="margin:5px 0 0;font-size:13px;">Astrotring</p>
                        </td>
                    </tr>

                    <!-- MESSAGE -->
                    <tr>
                        <td style="padding:20px;text-align:center;">

                            <h3 style="color:#c62828;margin:0;">Your Order Has Been Cancelled</h3>

                            <p style="color:#555;font-size:14px;">
                                Hi <b>{{ $order->user->name }}</b>,<br><br>

                                Your order <b>#{{ $order->order_number }}</b> has been successfully cancelled.
                            </p>

                        </td>
                    </tr>

                    <!-- REFUND BREAKDOWN -->
                    <tr>
                        <td style="padding:15px;">

                            <h3 style="color:#c62828;">💰 Refund Details</h3>

                            <table width="100%"
                                style="background:#fff5f5;border-radius:10px;padding:15px;font-size:14px;">

                                <tr>
                                    <td>Wallet Amount Used</td>
                                    <td align="right"><b>₹{{ $order->wallet_used }}</b></td>
                                </tr>

                                <tr>
                                    <td>Online Payment</td>
                                    <td align="right"><b>₹{{ $order->paid_amount }}</b></td>
                                </tr>

                            </table>

                            <p style="margin-top:10px;color:#555;font-size:13px;line-height:1.6;">
                                ✔ ₹{{ $order->wallet_used }} has been <b>instantly credited back to your
                                    wallet</b>.<br><br>

                                ⏳ ₹{{ $order->paid_amount }} will be <b>refunded to your original payment method</b>
                                (via Razorpay).<br><br>

                                🕒 This may take <b>24–48 hours</b> depending on your bank.
                            </p>

                        </td>
                    </tr>

                    <!-- PRODUCTS -->
                    <tr>
                        <td style="padding:15px;">
                            <h3 style="color:#c62828;">📦 Cancelled Items</h3>

                            @foreach($order->items as $item)
                            <table width="100%" style="border-bottom:1px solid #eee;margin-bottom:10px;">
                                <tr>

                                    <td width="70">
                                        <img src="{{ env('APP_URL') }}/storage/product/{{ $item->product->image }}"
                                            width="60" style="border-radius:6px;">
                                    </td>

                                    <td style="font-size:13px;">
                                        <b>{{ $item->product_name }}</b><br>
                                        Qty: {{ $item->quantity }}
                                    </td>

                                    <td align="right" style="color:#c62828;">
                                        Cancelled
                                    </td>

                                </tr>
                            </table>
                            @endforeach

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background:#ffe0b2;text-align:center;padding:15px;font-size:12px;">

                            🙏 Thank you for choosing Astrotring<br>

                            <span style="color:#777;">
                                For any help, contact our support team.
                            </span>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
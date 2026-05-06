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
                            style="background:linear-gradient(135deg,#e65100,#fb8c00);padding:22px;text-align:center;color:#fff;">

                            <h2 style="margin:0;">🔐 Astrotring Shop</h2>

                            <p style="margin:5px 0 0;font-size:13px;">
                                ✨ Secure Login Verification ✨
                            </p>

                        </td>
                    </tr>

                    <!-- OTP SECTION -->
                    <tr>
                        <td style="padding:25px;text-align:center;">

                            <h2 style="margin:0;color:#e65100;">
                                Login OTP Verification
                            </h2>

                            <p style="color:#555;font-size:14px;line-height:24px;">
                                Hi <b>{{ $name }}</b>,<br>
                                Use the following OTP to securely login into your Astrotring account.
                            </p>

                            <!-- OTP BOX -->
                            <div style="
                                margin:30px auto;
                                width:220px;
                                background:#fff8f0;
                                border:2px dashed #fb8c00;
                                border-radius:12px;
                                padding:18px;
                                font-size:34px;
                                font-weight:bold;
                                letter-spacing:8px;
                                color:#e65100;
                            ">
                                {{ $otp }}
                            </div>

                            <p style="font-size:13px;color:#777;">
                                ⏰ This OTP is valid for 10 minutes only.
                            </p>

                            <p style="font-size:13px;color:#999;line-height:22px;">
                                If you did not request this login,<br>
                                please ignore this email for your account safety.
                            </p>

                        </td>
                    </tr>

                    <!-- SECURITY BOX -->
                    <tr>
                        <td style="padding:0 20px 20px 20px;">

                            <table width="100%"
                                style="background:#fffaf5;border:1px solid #ffe0b2;border-radius:10px;padding:12px;">

                                <tr>
                                    <td style="font-size:13px;color:#666;line-height:22px;">

                                        🔒 Never share your OTP with anyone.<br>
                                        🛡️ Astrotring team will never ask for your OTP.

                                    </td>
                                </tr>

                            </table>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background:#ffe0b2;text-align:center;padding:15px;font-size:12px;color:#666;">

                            🙏 Thank you for trusting Astrotring Shop 💫<br>
                            We wish you positivity & success 🌟

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
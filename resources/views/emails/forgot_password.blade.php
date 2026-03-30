<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Forgot Password | {{ config('app.name') }}</title>
</head>

<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:Arial,Helvetica,sans-serif;">

    <table align="center" width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;background:#f5f5f5;">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:8px;padding:30px;">

                    <!-- Heading -->
                    <tr>
                        <td align="center" style="font-size:22px;font-weight:bold;color:#333;">
                            Reset Password
                        </td>
                    </tr>

                    <!-- Greeting -->
                    <tr>
                        <td align="center" style="padding-top:20px;font-size:16px;color:#666;">
                            Hi {{ $mailData['name'] }},
                        </td>
                    </tr>

                    <!-- Message -->
                    <tr>
                        <td align="center" style="padding-top:15px;font-size:15px;color:#777;line-height:1.6;">
                            We received a request to reset your password.
                            Please use the OTP below to continue.
                        </td>
                    </tr>

                    <!-- OTP Box -->
                    <tr>
                        <td align="center" style="padding:30px 0;">
                            <div
                                style="display:inline-block; background:#0DA487; color:#ffffff; padding:15px 40px; font-size:28px; font-weight:bold; border-radius:6px; letter-spacing:6px; font-family:monospace;">
                                {{ $mailData['otp'] }}
                            </div>
                        </td>
                    </tr>

                    <!-- Expiry -->
                    <tr>
                        <td align="center" style="font-size:14px;color:#888;line-height:1.6;">
                            This OTP is valid for <strong>10 minutes</strong>.<br>
                            Do not share this OTP with anyone.
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding-top:25px;font-size:13px;color:#aaa;">
                            If you did not request this, you can safely ignore this email.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>

@extends('layouts.emails.master')

@section('title')
<title>Verify Your OTP for Login | {{env('APP_NAME')}}</title>
@endsection

@section('style')
@endsection

@section('content')

    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 35px auto 0;">
                    <tr>
                        <td><img src="https://themes.pixelstrap.com/fastkart/email-templete/images/reset.png" alt=""></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 35px auto 0;">
                    <tr>
                        <td>
                            <h3 style="font-weight: 700; font-size: 20px; margin: 0;text-align: center;">Verify Your OTP for Login</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <h3 style="font-weight: 700; font-size: 20px; margin: 0;color: #939393;margin-top: 15px;text-align: center;"> Hi {{ $mailData['name'] }},</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="font-size: 17px;font-weight: 600;width: 74%;margin: 8px auto 0;line-height: 1.5;color: #939393;text-align: center;">Thank you for choosing our system. To ensure the security of your account, we require a one-time verification code (OTP) for login.</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="font-size: 17px;font-weight: 600;width: 74%;margin: 8px auto 0;line-height: 1.5;color: #939393;text-align: center;">Your OTP is: {{ $mailData['otp'] }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 35px auto 0;">
                    <tr>
                        <td>
                            <p style="font-size: 17px;font-weight: 600;width: 74%;margin: 8px auto 0;line-height: 1.5;color: #939393;text-align: center;"> If you did not attempt to log in or received this email in error, please disregard it.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

@endsection

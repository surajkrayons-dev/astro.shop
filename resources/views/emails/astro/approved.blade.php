<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Account Approved</title>
</head>

<body style="margin:0; padding:0; background:#f4f6f9; font-family:Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
        <tr>
            <td align="center">

                <table width="650" cellpadding="0" cellspacing="0"
                    style="background:#ffffff; border-radius:8px; overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background:#28a745; padding:20px; text-align:center; color:#ffffff;">
                            <h2 style="margin:0;">Your Astrologer Account Has Been Approved</h2>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px; font-size:14px; color:#444;">

                            <p>Dear <strong>{{ $user->name }}</strong>,</p>

                            <p>
                                Congratulations! Your astrologer account has been successfully approved.
                                Below are your registered details:
                            </p>

                            <!-- Details Table -->
                            <table width="100%" cellpadding="8" cellspacing="0"
                                style="border-collapse:collapse; margin-top:15px;">

                                <tr style="border-bottom:1px solid #eee;">
                                    <th align="left" width="40%">Name</th>
                                    <td>{{ $user->name }}</td>
                                </tr>

                                <tr style="border-bottom:1px solid #eee;">
                                    <th align="left">Email</th>
                                    <td>{{ $user->email }}</td>
                                </tr>

                                <tr style="border-bottom:1px solid #eee;">
                                    <th align="left">Username</th>
                                    <td>{{ $user->username }}</td>
                                </tr>

                                <tr style="border-bottom:1px solid #eee;">
                                    <th align="left">Mobile</th>
                                    <td>{{ $user->country_code }} {{ $user->mobile }}</td>
                                </tr>

                                <tr style="border-bottom:1px solid #eee;">
                                    <th align="left">Category</th>
                                    <td>{{ is_array($user->category) ? implode(', ', $user->category) : $user->category }}
                                    </td>
                                </tr>

                                <tr style="border-bottom:1px solid #eee;">
                                    <th align="left">Experience</th>
                                    <td>{{ $user->experience }} Years</td>
                                </tr>

                                <tr style="border-bottom:1px solid #eee;">
                                    <th align="left">Expertise</th>
                                    <td>{{ is_array($user->expertise) ? implode(', ', $user->expertise) : $user->expertise }}
                                    </td>
                                </tr>

                                <tr style="border-bottom:1px solid #eee;">
                                    <th align="left">Qualification</th>
                                    <td>{{ is_array($user->qualification) ? implode(', ', $user->qualification) : $user->qualification }}
                                    </td>
                                </tr>

                                <tr style="border-bottom:1px solid #eee;">
                                    <th align="left">Languages</th>
                                    <td>{{ is_array($user->languages) ? implode(', ', $user->languages) : $user->languages }}
                                    </td>
                                </tr>

                                <tr style="border-bottom:1px solid #eee;">
                                    <th align="left">Daily Available Hours</th>
                                    <td>{{ $user->daily_available_hours }}</td>
                                </tr>

                                <tr style="border-bottom:1px solid #eee;">
                                    <th align="left">Is Family Astrologer</th>
                                    <td>{{ $user->is_family_astrologer ? 'Yes' : 'No' }}</td>
                                </tr>

                                @if ($user->is_family_astrologer && !empty($user->family_astrology_details))
                                    <tr style="border-bottom:1px solid #eee;">
                                        <th align="left">Family Astrology Details</th>
                                        <td>{{ $user->family_astrology_details }}</td>
                                    </tr>
                                @endif

                                <tr>
                                    <th align="left">Account Status</th>
                                    <td>
                                        <span
                                            style="background:#28a745; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px;">
                                            Active
                                        </span>
                                    </td>
                                </tr>

                            </table>

                            <div style="text-align:center; margin-top:30px;">
                                <a href="{{ $loginUrl }}"
                                    style="background:#28a745;
                              color:#ffffff;
                              padding:12px 28px;
                              text-decoration:none;
                              border-radius:5px;
                              font-weight:bold;
                              display:inline-block;">
                                    Login to Your Account
                                </a>
                            </div>

                            <p style="margin-top:25px; font-size:13px; color:#777;">
                                Please keep your login credentials secure.
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="background:#f8f9fa; padding:15px; text-align:center; font-size:12px; color:#777;">
                            © {{ date('Y') }} Astrologer Platform. All rights reserved.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>

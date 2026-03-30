<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        @yield('title')

        <link rel="icon" href="images/favicon.png" type="image/x-icon">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@200;300;400;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

        <style type="text/css">
            body {
                text-align: center;
                margin: 0 auto;
                width: 650px;
                font-family: 'Public Sans', sans-serif;
                background-color: #e2e2e2;
                display: block;
            }

            ul {
                margin: 0;
                padding: 0;
            }

            li {
                display: inline-block;
                text-decoration: unset;
            }

            a {
                text-decoration: none;
            }

            h5 {
                margin: 10px;
                color: #777;
            }

            .theme-color {
                color: #0DA487;
            }
        </style>

        @yield('style')

    </head>

    <body style="margin: 20px auto;">
        <table align="center" border="1" cellpadding="0" cellspacing="0" style="background-color: white; width: 100%; box-shadow: 0px 0px 14px -4px rgba(0, 0, 0, 0.2705882353);-webkit-box-shadow: 0px 0px 14px -4px rgba(0, 0, 0, 0.2705882353);">
            <tbody>
                <tr>
                    <td>
                        @include('layouts.emails.partials.header')

                        @yield('content')

                        @include('layouts.emails.partials.footer')
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
    </html>

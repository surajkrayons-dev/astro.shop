<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Login Requested | VR Enterprises CRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="author" content="Abhisan Technology" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ url('assets/images/favicon.ico') }}">

    <!-- Bootstrap Css -->
    <link href="{{ url('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />

    <!-- jQuery -->
    <script src="{{ url('assets/libs/jquery/jquery.min.js') }}"></script>
</head>
<body>
    <div class="d-flex align-items-center justify-content-center vh-100">
        <div class="text-center">
            <div class="mb-5"><img src="{{ url('assets/images/verify-login.png') }}" alt="" width="150"></div>
            
            <h1 class="jumbotron-heading">Login Requested</h1>
            <p class="lead">Dear {{ auth()->user()->name }}, Your login request has been raised and informed to Admin. Please wait for approval.</p>
            <p>
              <a href="{{ route("admin.dashboard.login.requested") }}" class="btn btn-primary my-2">Refresh Page</a>
              <a href="{{ route("admin.profile.logout") }}" class="btn btn-secondary my-2">Logout</a>
            </p>
        </div>
    </div>

    <script>
        getLoginStatus();
        setInterval(getLoginStatus, 5000);
        
        function getLoginStatus() {
            $.get('{{ route("admin.dashboard.login.request.status") }}', ({status}) => {
                if (status != 'pending') {
                    location.reload(true);
                }
            }, 'json');
        }
    </script>
</body>
</html>
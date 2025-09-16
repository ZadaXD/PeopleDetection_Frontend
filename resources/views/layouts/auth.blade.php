<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - {{ config('app.name') }}</title>
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  @yield('content')

  <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
  <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
</body>
</html>

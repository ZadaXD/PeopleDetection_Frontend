<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            {{-- Sidebar --}}
            {{-- @include('partials.sidebar') --}}

            <div class="layout-page">
                {{-- Navbar --}}
                @include('partials.navbar')

                <div class="content-wrapper">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
</body>

</html>

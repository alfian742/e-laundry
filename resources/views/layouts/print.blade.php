<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ $site->site_name }} | @yield('title')</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ get_image_url($site->logo) }}">
    <link rel="apple-touch-icon" href="{{ get_image_url($site->logo) }}">

    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{ asset('modules/bootstrap/css/bootstrap.min.css') }}">

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <style>
        html,
        body {
            font-family: Arial, Helvetica, sans-serif;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-white">
    <div id="app">
        <div class="main-wrapper">
            <div class="container-fluid p-4">
                <header class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center" style="gap: .5rem">
                        <img src="{{ get_image_url($site->logo) }}" alt="Logo" class="rounded" height="40"
                            width="40" style="object-fit: cover">
                        <h5 class="font-weight-bold mb-0">{{ $site->site_name }}</h5>
                    </div>
                    <span class="text-small text-right">
                        Dicetak: {{ carbon_format_date(now(), 'datetime') . " {$zone}" }}
                        <br>
                        Oleh:
                        {{ ($isOwner || $isAdmin || $isEmployee) && Auth::user()->relatedStaff?->fullname ? Auth::user()->relatedStaff->fullname : 'N/A' }}
                    </span>
                </header>

                <hr class="mt-3 mb-4">

                <!-- Content -->
                @yield('main')
                <!-- End content -->
            </div>
        </div>
    </div>

    <!-- General JS Scripts -->
    <script src="{{ asset('modules/jquery.min.js') }}"></script>
    <script src="{{ asset('modules/popper.js') }}"></script>
    <script src="{{ asset('modules/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/stisla.js') }}"></script>

    <!-- Template JS File -->
    <script src="{{ asset('js/scripts.js') }}"></script>

    @stack('scripts')
</body>

</html>

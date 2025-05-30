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
    <link rel="stylesheet" href="{{ asset('modules/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/aos/dist/aos.css') }}">

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    @stack('styles')
</head>

<body class="layout-3">
    <div id="app">
        <div class="main-wrapper">
            <!-- Navbar -->
            @include('components.landing-navbar')

            <!-- Main Content -->
            @yield('main')

            <!-- Footer -->
            @include('components.landing-footer')

            <div id="scrollToTopPage" class="d-none scroll-to-top-landing-page">
                <a href="#app" class="btn btn-primary shadow-sm scroll-link" data-toggle="tooltip" title="Ke atas">
                    <i class="fa-solid fa-arrow-up"></i>
                </a>
            </div>

            @include('components.help-center-modal')
        </div>
    </div>

    <!-- General JS Scripts -->
    <script src="{{ asset('modules/jquery.min.js') }}"></script>
    <script src="{{ asset('modules/popper.js') }}"></script>
    <script src="{{ asset('modules/tooltip.js') }}"></script>
    <script src="{{ asset('modules/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('modules/moment.min.js') }}"></script>
    <script src="{{ asset('js/stisla.js') }}"></script>

    <!-- JS Libraies -->
    <script src="{{ asset('modules/sweetalert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('modules/aos/dist/aos.js') }}"></script>

    <!-- Template JS File -->
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>

    @stack('scripts')

    <script>
        // AOS library 
        AOS.init();
    </script>
</body>

</html>

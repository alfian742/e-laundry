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

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <style>
        .auth-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 50vw;
        }

        @media screen and (max-width: 992px) {
            .auth-wrapper {
                width: 100%;
            }
        }

        .auth-background {
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            width: 100% !important;
        }

        .min-vh-100 {
            min-height: 100vh;
        }

        .overlay-gradient-bottom:after {
            content: " ";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: false;
            background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0) 65%, rgba(0, 0, 0, 0.65) 100%);
            z-index: 1;
        }

        .absolute-bottom-left {
            position: absolute;
            left: 0;
            bottom: 0;
            z-index: 2 !important;
        }
    </style>

    @stack('styles')
</head>

<body>
    <div id="app">
        <div class="main-wrapper">
            <div class="d-flex align-items-stretch">
                <div class="auth-wrapper min-vh-100 bg-white">
                    <div class="row g-4 p-5 m-0">
                        <div class="col-12 mb-4">
                            <div class="d-flex justify-content-center align-items-center mb-3" style="gap: .5rem">
                                <img src="{{ get_image_url($site->logo) }}" alt="Logo" class="rounded"
                                    height="24" width="24" style="object-fit: cover">
                                <h5 class="font-weight-bold mb-0">{{ $site->site_name }}</h5>
                            </div>

                            <h2 class="text-center text-dark">
                                {{ Request::is('register') ? 'Registrasi' : 'Masuk' }}
                            </h2>
                        </div>
                        <div class="col-12 mb-3">
                            @if (session()->has('success'))
                                <div class="alert alert-success alert-dismissible fade show text-center mb-4"
                                    role="alert">
                                    <i class='fa-solid fa-circle-check mr-1'></i> {{ session('success') }}
                                </div>
                            @endif

                            @if (session()->has('error'))
                                <div class="alert alert-danger alert-dismissible fade show text-center mb-4"
                                    role="alert">
                                    <i class='fa-solid fa-circle-xmark mr-1'></i> {{ session('error') }}
                                </div>
                            @endif

                            <!-- Content -->
                            @yield('main')
                            <!-- End content -->
                        </div>
                        <div class="col-12">
                            <div class="text-center">
                                <a href="{{ url('/') }}"><i class="fas fa-arrow-left mr-1"></i>
                                    Kembali ke Beranda
                                </a>
                            </div>

                            <div class="text-center text-small mt-5">
                                &copy; {{ date('Y') }} {{ $site->site_name }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="auth-background min-vh-100 overlay-gradient-bottom d-none d-lg-inline position-relative"
                    data-background="{{ asset('img/static/auth-image.jpg') }}">
                    <div class="absolute-bottom-left" style="left: 1rem; bottom: 1rem">
                        <div class="text-light">
                            Sumber: <a class="text-light bb"
                                href="https://www.freepik.com/free-photo/washing-machine-minimal-laundry-room-interior-design_19002692.htm#fromView=image_search_similar&page=1&position=0&uuid=8fccba32-9fe1-456a-9bb2-0f5551910d64"
                                target="_blank">Freepik</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help center modal button -->
        <div class="position-fixed" style="bottom: 1rem; right: 1rem; z-index: 99999;" data-toggle="tooltip"
            title="Pusat Bantuan">
            <button type="button" class="btn btn-lg btn-success p-2 shadow-sm" data-toggle="modal"
                data-target="#helpCenterModal">
                <i class="fa-solid fa-comments" style="font-size: 24px"></i>
            </button>
        </div>

        @include('components.help-center-modal')
    </div>

    <!-- General JS Scripts -->
    <script src="{{ asset('modules/jquery.min.js') }}"></script>
    <script src="{{ asset('modules/popper.js') }}"></script>
    <script src="{{ asset('modules/tooltip.js') }}"></script>
    <script src="{{ asset('modules/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('modules/moment.min.js') }}"></script>
    <script src="{{ asset('js/stisla.js') }}"></script>

    <!-- Template JS File -->
    <script src="{{ asset('js/scripts.js') }}"></script>

    @stack('scripts')
</body>

</html>

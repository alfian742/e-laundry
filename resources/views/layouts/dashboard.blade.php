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
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    @stack('styles')
</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>

            <!-- Navbar -->
            @include('components.dashboard-navbar')

            <!-- Sidebar -->
            @include('components.dashboard-sidebar')

            <!-- Main Content -->
            @yield('main')

            <!-- Footer -->
            @include('components.dashboard-footer')
        </div>

        <div id="scrollToTopPage" class="d-none">
            <a href="#app" class="btn btn-primary shadow-sm scroll-link" data-toggle="tooltip" title="Ke atas">
                <i class="fa-solid fa-arrow-up"></i>
            </a>
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

    <!-- JS Libraies -->
    <script src="{{ asset('modules/sweetalert/sweetalert.min.js') }}"></script>

    <!-- Template JS File -->
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>

    @stack('scripts')

    <script>
        // Handle logout
        function handleLogout(event) {
            event.preventDefault();

            swal({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda ingin mengakhiri sesi ini?',
                icon: 'warning',
                buttons: {
                    cancel: 'Batal',
                    confirm: {
                        text: 'Ya, keluar!',
                        value: true,
                    }
                }
            }).then((result) => {
                if (result) {
                    document.getElementById('logout-form').submit();
                }
            });
        }

        // Handle messages
        document.addEventListener('DOMContentLoaded', function() {
            let title = '';
            let htmlContent = '';
            let icon = '';
            let url = '';

            @if (session('success-with-url'))
                title = 'Berhasil!';
                htmlContent = `{!! session('success-with-url') !!}`;
                icon = 'success';
                url = `{!! session('url') !!}`;
            @elseif (session('success'))
                title = 'Berhasil!';
                htmlContent = `{!! session('success') !!}`;
                icon = 'success';
            @elseif (session('error'))
                title = 'Gagal!';
                htmlContent = `{!! session('error') !!}`;
                icon = 'error';
            @elseif (session('warning'))
                title = 'Peringatan!';
                htmlContent = `{!! session('warning') !!}`;
                icon = 'warning';
            @endif

            if (title && htmlContent && icon) {
                const content = document.createElement('div');
                content.innerHTML = htmlContent;

                if (url) {
                    // If there is a URL, display 'Close' and 'Send Message' buttons
                    swal({
                        title: title,
                        content: content,
                        icon: icon,
                        buttons: {
                            cancel: {
                                text: 'Tutup',
                                value: null,
                                visible: true,
                                className: 'cancel-button',
                                closeModal: true
                            },
                            confirm: {
                                text: 'Kirim Pesan',
                                value: true,
                                visible: true,
                                className: 'confirm-button',
                                closeModal: true
                            }
                        }
                    }).then((value) => {
                        if (value) {
                            window.open(url, '_blank'); // Open the URL in a new tab
                        }
                    });
                } else {
                    // If there is no URL, display 'OK' button with a 5-second timer
                    swal({
                        title: title,
                        content: content,
                        icon: icon,
                        button: "OK",
                        timer: 5000
                    });
                }
            }
        });
    </script>

    @if (!$isCustomer)
        <!-- Handle notifications -->
        <script>
            let pollingInterval;
            let isPollingActive = false;
            let idleTime = 0;
            const idleLimit = 300; // 5 menit

            function getNotification() {
                $.ajax({
                    url: '{{ route('notifications.get') }}',
                    method: 'GET',
                    success: function(response) {
                        const lastCount = localStorage.getItem('lastNotifCount');
                        const total = parseInt(response.count) || 0;

                        if (lastCount != total) {
                            localStorage.setItem('lastNotifCount', total);

                            let displayCount = total > 9 ? '9+' : total;

                            if (total > 0) {
                                $('#notif-count-container').removeClass('d-none');
                                $('#notif-count').text(displayCount);
                            } else {
                                $('#notif-count-container').addClass('d-none');
                                $('#notif-count').text(0);
                            }
                        }
                    },
                    error: function() {
                        console.error('Gagal memuat notifikasi.');
                    }
                });
            }

            function startPolling() {
                if (!isPollingActive) {
                    pollingInterval = setInterval(() => {
                        if (!document.hidden) {
                            getNotification();
                        }
                    }, 60000); // 60 detik
                    isPollingActive = true;
                    console.log('Polling dimulai');
                }
            }

            function stopPolling() {
                if (isPollingActive) {
                    clearInterval(pollingInterval);
                    isPollingActive = false;
                    console.log('Polling dihentikan karena idle');
                }
            }

            function resetIdleTimer() {
                idleTime = 0;
                if (!isPollingActive) {
                    startPolling();
                }
            }

            function checkIdleStatus() {
                idleTime++;
                if (idleTime >= idleLimit) {
                    stopPolling();
                }
            }

            function debounce(func, delay) {
                let timer;
                return function() {
                    clearTimeout(timer);
                    timer = setTimeout(func, delay);
                };
            }

            $(document).ready(function() {
                // Tampilkan badge notifikasi dari localStorage saat halaman dibuka
                const lastCount = parseInt(localStorage.getItem('lastNotifCount')) || 0;
                const displayCount = lastCount > 9 ? '9+' : lastCount;

                if (lastCount > 0) {
                    $('#notif-count-container').removeClass('d-none');
                    $('#notif-count').text(displayCount);
                } else {
                    $('#notif-count-container').addClass('d-none');
                    $('#notif-count').text(0);
                }

                // Mulai polling
                getNotification();
                startPolling();

                // Cek idle
                setInterval(checkIdleStatus, 1000);
                $(this).on('mousemove keydown scroll click', debounce(resetIdleTimer, 500));
            });
        </script>
    @endif
</body>

</html>

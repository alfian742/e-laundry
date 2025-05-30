<div class="navbar-bg custom-navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
    <div class="container">
        <a href="{{ url('/') }}" class="navbar-brand custom-navbar-brand ml-2">
            <img src="{{ get_image_url($site->logo) }}" alt="Logo" class="rounded" loading="eager" height="32"
                width="32" style="object-fit: cover">
            <span>{{ $site->site_name }}</span>
        </a>

        <ul class="navbar-nav ml-auto">
            @if (!is_null($site->facebook))
                <li>
                    <a href="{{ $site->facebook }}" class="nav-link nav-link-lg d-none d-lg-inline">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                </li>
            @endif
            @if (!is_null($site->instagram))
                <li>
                    <a href="{{ $site->instagram }}" class="nav-link nav-link-lg d-none d-lg-inline">
                        <i class="fab fa-instagram"></i>
                    </a>
                </li>
            @endif
            @if (!is_null($site->tiktok))
                <li>
                    <a href="{{ $site->tiktok }}" class="nav-link nav-link-lg d-none d-lg-inline">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </li>
            @endif
            <li>
                <a href="#" class="nav-link sidebar-gone-show custom-toogle-sidebar" data-toggle="sidebar">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>

<nav class="navbar navbar-secondary navbar-expand-lg">
    <div class="container">
        <ul class="navbar-nav">
            <li class="nav-item {{ Request::is('/') ? 'active' : '' }}">
                <a href="{{ url('/') }}" class="nav-link"><i class="fas fa-house"></i><span>Beranda</span></a>
            </li>
            <li class="nav-item {{ Request::is('landing/service*') ? 'active' : '' }}">
                <a href="{{ url('/landing/service') }}" class="nav-link"><i
                        class="fas fa-jug-detergent"></i><span>Layanan
                        Laundry</span></a>
            </li>
            <li class="nav-item {{ Request::is('landing/check-order*') ? 'active' : '' }}">
                <a href="{{ url('/landing/check-order') }}" class="nav-link"><i class="fas fa-dolly"></i><span>Cek
                        Pesanan</span></a>
            </li>
        </ul>
        <div class="my-4 ml-lg-auto mr-lg-2 px-3 px-lg-0 hide-sidebar-mini">
            <!-- Help center modal button -->
            <button type="button" class="btn btn-success btn-block btn-icon icon-left" data-toggle="modal"
                data-target="#helpCenterModal">
                <i class="fa-solid fa-comments"></i> <span class="ml-1 d-lg-none">Pusat Bantuan</span>
            </button>
        </div>
        <div class="my-4 px-3 px-lg-0 hide-sidebar-mini">
            <a href="{{ url('/login') }}" class="btn btn-outline-primary btn-block btn-icon icon-left">
                <i class="fas fa-sign-in-alt"></i> <span class="ml-1">Masuk</span></a>
        </div>
    </div>
</nav>

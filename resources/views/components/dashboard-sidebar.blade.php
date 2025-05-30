<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ url('/dashboard') }}">{{ $site->site_name }}</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ url('/dashboard') }}">
                <img src="{{ get_image_url($site->logo) }}" alt="Logo" class="rounded" height="32" width="32"
                    style="object-fit: cover">
            </a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Umum</li>
            <li class="{{ Request::is('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/dashboard') }}"><i class="fas fa-fire"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            @if ($isCustomer)
                <li class="menu-header">Layanan</li>
                <li class="{{ Request::is('order/services') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('/order/services') }}">
                        <i class="fas fa-jug-detergent"></i>
                        <span>Layanan Laundry</span>
                    </a>
                </li>
            @endif

            <li class="menu-header">Pesanan</li>
            <li
                class="{{ Request::is('order*') && (!Request::is('order/services') && !Request::is('order/cart')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/order') }}">
                    <i class="fas fa-dolly"></i>
                    <span>Pesanan</span>
                </a>
            </li>

            <li class="{{ Request::is('pickup-delivery-schedule*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/pickup-delivery-schedule') }}">
                    <i class="fas fa-truck"></i>
                    <span>Jadwal Antar/Jemput</span>
                </a>
            </li>

            @if ($isOwner || $isAdmin)
                <li class="menu-header">Keuangan</li>
                <li class="{{ Request::is('revenue*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('/revenue') }}">
                        <i class="fas fa-money-bill-transfer"></i>
                        <span>Pendapatan</span>
                    </a>
                </li>
                <li class="{{ Request::is('expense*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('/expense') }}">
                        <i class="fas fa-money-bill-transfer"></i>
                        <span>Pengeluaran</span>
                    </a>
                </li>
            @endif

            <li class="menu-header">Pengguna</li>
            <li class="{{ Request::is('customer-review*') ? 'active' : '' }}">
                <a class="nav-link"
                    href="{{ $isCustomer ? url('/customer-review/my-review') : url('/customer-review') }}">
                    <i class="fas fa-comment"></i>
                    <span>Ulasan Pelanggan</span>
                </a>
            </li>

            @if (!$isCustomer)
                <li class="{{ Request::is('customer*') && !Request::is('customer-review*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('/customer') }}">
                        <i class="fas fa-user"></i>
                        <span>Data Pelanggan</span>
                    </a>
                </li>

                <li class="{{ Request::is('staff*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('/staff') }}">
                        <i class="fas fa-user-tie"></i>
                        <span>Data Staf</span>
                    </a>
                </li>

                @if ($isOwner || $isAdmin)
                    <li class="{{ Request::is('account*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('/account') }}">
                            <i class="fas fa-users"></i>
                            <span>Data Akun</span>
                        </a>
                    </li>
                @endif

                <li class="menu-header">Layanan</li>
                <li class="{{ Request::is('service*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('/service') }}">
                        <i class="fas fa-jug-detergent"></i>
                        <span>Layanan Laundry</span>
                    </a>
                </li>
                <li class="{{ Request::is('promo*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('/promo') }}">
                        <i class="fas fa-gift"></i>
                        <span>Promo</span>
                    </a>
                </li>

                <li class="{{ Request::is('delivery-method*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('/delivery-method') }}">
                        <i class="fas fa-truck"></i>
                        <span>Metode Antar/Jemput</span>
                    </a>
                </li>

                <li class="{{ Request::is('payment-method*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('/payment-method') }}">
                        <i class="fas fa-credit-card"></i>
                        <span>Metode Pembayaran</span>
                    </a>
                </li>
            @endif

            @if (!$isCustomer && !$isEmployee)
                <li class="menu-header">Pengaturan</li>
                <li class="{{ Request::is('site-identity*') ? 'active' : '' }}">
                    <a href="{{ url('/site-identity') }}" class="nav-link">
                        <i class="fas fa-globe"></i>
                        <span>Situs</span>
                    </a>
                </li>
            @endif
        </ul>

        <div class="my-4 px-3 hide-sidebar-mini">
            <!-- Help center modal button -->
            <button type="button" class="btn btn-success btn-lg btn-block btn-icon-split" data-toggle="modal"
                data-target="#helpCenterModal">
                <i class="fa-solid fa-comments"></i> Pusat Bantuan
            </button>
        </div>
    </aside>
</div>

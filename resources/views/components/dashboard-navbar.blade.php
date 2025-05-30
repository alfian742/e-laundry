<nav class="navbar navbar-expand-lg main-navbar">
    <ul class="navbar-nav mr-auto">
        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
    </ul>
    <ul class="navbar-nav navbar-right">
        @if (!$isCustomer)
            <li>
                <a href="{{ url('/notification') }}" class="nav-link nav-link-lg">
                    <div class="position-relative d-inline-block">
                        <i class="far fa-bell position-relative" style="font-size: 1.5rem;"></i>

                        <div id="notif-count-container" class="d-none">
                            <span id="notif-count"
                                class="position-absolute bg-warning text-white rounded d-flex justify-content-center align-items-center"
                                style="top: -0.4rem; right: -0.6rem; width: 1.2rem; height: 1.2rem; font-size: 0.7rem;">
                                0
                            </span>
                        </div>
                    </div>
                </a>
            </li>
        @else
            <li>
                <a href="{{ url('/order/cart') }}" class="nav-link nav-link-lg">
                    <div class="position-relative d-inline-block">
                        <i class="fas fa-shopping-cart position-relative" style="font-size: 1.5rem;"></i>

                        @if ($cartItemCount > 0)
                            <span
                                class="position-absolute bg-warning text-white rounded d-flex justify-content-center align-items-center"
                                style="top: -0.4rem; right: -0.6rem; width: 1.2rem; height: 1.2rem; font-size: 0.7rem;">
                                {{ $cartItemCount > 9 ? '9+' : $cartItemCount }}
                            </span>
                        @endif
                    </div>
                </a>
            </li>
        @endif
        <li class="dropdown">
            @php
                $user = Auth::user();
                if ($isOwner || $isAdmin || $isEmployee):
                    $fullname = $user->relatedStaff->fullname ?? 'N/A';
                    $urlProfile = url('/staff/profile') ?? '#';
                elseif ($isCustomer):
                    $fullname = $user->relatedCustomer->fullname ?? 'N/A';
                    $urlProfile = url('/customer/profile') ?? '#';
                endif;
            @endphp
            <a href="#" data-toggle="dropdown"
                class="nav-link dropdown-toggle dropdown-toggle-custom nav-link-lg nav-link-user">
                <div class="d-sm-none d-lg-inline-block">{{ $fullname }}</div>
                <img class="avatar-initials avatar-initial-default ml-1" data-name="{{ $fullname }}">
            </a>
            <div class="dropdown-menu dropdown-menu-right mr-2">
                <a href="{{ $urlProfile }}" class="dropdown-item has-icon">
                    <i class="fas fa-user"></i> Profil
                </a>

                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item has-icon text-danger" onclick="handleLogout(event)">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </a>
                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                    @csrf
                </form>
            </div>
        </li>
    </ul>
</nav>

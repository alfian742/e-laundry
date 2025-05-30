<div class="main-footer custom-main-footer bg-white border-0">
    <div class="container custom-container-footer">
        <div class="row">
            <div class="mb-4 col-lg-4 col-md-6">
                <div class="row">
                    <div class="col-lg-3">
                        <img src="{{ get_image_url($site->logo) }}" alt="Logo" class="rounded d-inline-block mb-2"
                            height="64" width="64" style="object-fit: cover" loading="lazy">
                    </div>
                    <div class="col-lg-9">
                        <h4 class="mb-2">{{ $site->site_name }}</h4>
                        <h6 class="mb-4">{{ $site->tagline }}</h6>
                    </div>
                </div>

                <div class="mb-2">
                    <i class="fa-solid fa-map-location-dot" style="width: 1rem"></i> <span
                        class="ml-1">{{ $site->address }}</span>
                </div>

                <div>
                    <i class="fa-solid fa-clock" style="width: 1rem"></i> <span
                        class="ml-1">{{ $site->operational_hours }}</span>
                </div>
            </div>

            <div class="mb-4 col-lg-3 col-md-6">
                <h5 class="mb-2">Menu</h5>

                <div class="text-left">
                    <a href="{{ url('/') }}" class="btn btn-link">Beranda</a>
                    <br>
                    <a href="{{ url('/landing/service') }}" class="btn btn-link">Layanan Laundry</a>
                    <br>
                    <a href="{{ url('/landing/check-order') }}" class="btn btn-link">Cek Pesanan</a>
                    <br>
                    <a href="{{ url('/login') }}" class="btn btn-link">Masuk</a>
                </div>
            </div>

            <div class="mb-4 col-lg-3 col-md-6 col-sm-6">
                <h5 class="mb-2">Hubungi Kami</h5>

                @if (!is_null($site->email))
                    <a href="{{ 'mailto:' . $site->email }}" class="btn btn-icon icon-left">
                        <i class="fas fa-envelope mr-1"></i> {{ $site->email }}</a>
                    <br>
                @endif
                @if (!is_null($site->phone_number))
                    <a href="{{ 'https://wa.me/' . formatPhoneNumber($site->phone_number) }}" target="_blank"
                        class="btn btn-icon icon-left">
                        <i class="fab fa-whatsapp mr-1"></i> {{ $site->phone_number }}</a>
                @endif
            </div>

            <div class="mb-4 col-lg-2 col-md-6 col-sm-6">
                <h5 class="mb-2">Ikuti kami</h5>

                <div class="d-flex flex-wrap" style="gap: .5rem">
                    @if (!is_null($site->facebook))
                        <a href="{{ $site->facebook }}" class="btn btn-outline-primary">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    @endif
                    @if (!is_null($site->instagram))
                        <a href="{{ $site->instagram }}" class="btn btn-outline-danger">
                            <i class="fab fa-instagram"></i>
                        </a>
                    @endif
                    @if (!is_null($site->tiktok))
                        <a href="{{ $site->tiktok }}" class="btn btn-outline-dark">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <footer class="border-top py-3">
            <div class="footer-left">
                &copy; {{ date('Y') }} {{ $site->site_name }}
            </div>
            <div class="footer-right">
                Dibuat oleh <a href="#">Developers</a>
            </div>
        </footer>
    </div>
</div>

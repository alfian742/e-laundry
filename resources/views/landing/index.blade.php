@extends('layouts.landing')

@section('title', 'Beranda')

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/owlcarousel2/dist/assets/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/owlcarousel2/dist/assets/owl.theme.default.min.css') }}">
@endpush

@section('main')
    <div class="main-content custom-main-content">
        <section class="section">
            <div class="section-body">
                <div class="row m-0 p-0">
                    <div class="col-12 m-0 p-0">
                        <div class="hero text-white hero-bg-image custom-hero-bg-image px-2 px-lg-0"
                            style="background-image: url('{{ asset('img/static/hero-image.jpg') }}');">
                            <div class="hero-inner container custom-container-hero text-center text-lg-left py-4">
                                <h1 class="hero-title">{{ $site->site_name }}</h1>
                                <h5><span class="d-none d-lg-inline">—</span> {{ $site->tagline }}</h5>
                                <div class="d-flex flex-row justify-content-center justify-content-lg-start"
                                    style="gap: .5rem; margin-top: 2.5rem;">
                                    <a href="#about" class="btn bg-white text-dark btn-lg scroll-link">
                                        <span class="d-none d-lg-inline">Lihat</span> Selengkapnya
                                    </a>
                                    <a href="{{ url('/login') }}" class="btn btn-outline-white btn-lg">
                                        Masuk
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 m-0 px-4 px-lg-0 bg-white about" id="about">
                        <div class="container custom-container-section py-4">
                            <div class="row align-items-center">
                                <div class="col-lg-3">
                                    <a target="_blank" rel="noopener noreferrer"
                                        href="https://www.freepik.com/free-photo/woman-using-washing-machine-doing-laundry-young-woman-ready-wash-clothes-interior-washing-process-concept_11183824.htm#fromView=search&page=3&position=14&uuid=f5ba83df-be45-4ef6-a433-41ae0a730e97&query=Laundry">
                                        <div class="ratio ratio-1x1 mx-auto mx-lg-0 mr-lg-auto" style="max-width: 240px;">
                                            <img src="{{ asset('img/static/about-image.jpg') }}" alt="About Image"
                                                loading="eager" class="rounded d-block w-100 h-100"
                                                style="object-fit: cover;" loading="lazy">
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-9">
                                    <h2 class="section-title mt-lg-0">Tentang Kami</h2>
                                    <article>
                                        {!! $site->about_us !!}
                                    </article>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container custom-container-section pt-5">
                    <div class="row justify-content-center align-iitems-center m-0 p-0 rounded shadow-sm"
                        style="overflow: hidden;">
                        <div class="col-lg-9 m-0 p-0">
                            <div class="row m-0 p-0">
                                <div class="col-lg-6 col-md-6 col-sm-12 m-0 p-0">
                                    <div class="card card-statistic-1 m-0 shadow-none rounded-0">
                                        <div class="card-icon bg-primary">
                                            <i class="fas fa-shirt"></i>
                                        </div>
                                        <div class="card-body custom-card-body-statistic-1 d-flex align-items-center">
                                            <h5 class="mb-0">Cuci Kiloan/Satuan</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12 m-0 p-0">
                                    <div class="card card-statistic-1 m-0 shadow-none rounded-0">
                                        <div class="card-icon bg-primary">
                                            <i class="fas fa-bolt"></i>
                                        </div>
                                        <div class="card-body custom-card-body-statistic-1 d-flex align-items-center">
                                            <h5 class="mb-0">Proses Cepat & Berkualitas</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12 m-0 p-0">
                                    <div class="card card-statistic-1 m-0 shadow-none rounded-0">
                                        <div class="card-icon bg-primary">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                        <div class="card-body custom-card-body-statistic-1 d-flex align-items-center">
                                            <h5 class="mb-0">Layanan Antar/Jemput</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12 m-0 p-0">
                                    <div class="card card-statistic-1 m-0 shadow-none rounded-0">
                                        <div class="card-icon bg-primary">
                                            <i class="fas fa-money-bill-transfer"></i>
                                        </div>
                                        <div class="card-body custom-card-body-statistic-1 d-flex align-items-center">
                                            <h5 class="mb-0">Pembayaran Tunai & Non Tunai</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 d-none d-lg-inline m-0 p-0">
                            <a target="_blank" rel="noopener noreferrer"
                                href="https://www.freepik.com/free-photo/mother-with-daughter-doing-laundry-self-serviece-laundrette_6636879.htm#fromView=image_search_similar&page=4&position=11&uuid=ac763868-0075-4635-9828-b18c0fa741e7">
                                <img src="{{ asset('img/static/list-service-image.jpg') }}" alt="About Image"
                                    loading="eager" class="d-block w-100 h-100" style="object-fit: cover;" loading="lazy">
                            </a>
                        </div>
                    </div>
                </div>

                <div class="container custom-container-section py-5">
                    <div class="row justify-content-center align-iitems-center">
                        <div class="col-12">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4"
                                style="gap: .5rem">
                                <h4 class="text-center mb-0">Layanan Kami</h4>
                                @if (!$services->isEmpty())
                                    <a href="{{ url('/landing/service') }}" class="btn btn-primary btn-icon icon-right">
                                        Lihat Lainnya <i class="fas fa-arrow-right"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        @forelse ($services as $service)
                            <div class="col-sm-6 col-md-4">
                                <div class="card">
                                    <div class="card-body p-0">
                                        <div class="custom-card-img-wrapper">
                                            <div class="custom-card-img-label m-2">
                                                @if ($service->active)
                                                    <span class="badge badge-success">Tersedia</span>
                                                @else
                                                    <span class="badge badge-danger">Tidak Tersedia</span>
                                                @endif
                                            </div>

                                            <!-- style sebagai parameter -->
                                            <div class="ratio ratio-4x3" style="max-width: 100%; margin: auto;">
                                                <img src="{{ get_image_url($service->img) }}"
                                                    alt="{{ $service->service_name ?? 'N/A' }}" loading="lazy"
                                                    class="rounded-top d-block w-100 h-100" style="object-fit: cover;">
                                            </div>
                                        </div>

                                        <div class="p-4">
                                            <h5 class="card-title text-clamp"
                                                title="{{ $service->service_name ?? 'N/A' }}">
                                                {{ $service->service_name ?? 'N/A' }}
                                            </h5>

                                            <div class="d-flex justify-content-between align-items-center mb-4"
                                                style="gap: .5rem">
                                                <h6 class="mb-0">{{ formatRupiah($service->price_per_kg) }}</h6>
                                                {!! !$service->promos->isEmpty() && $service->promos->where('active', true)->count() > 0
                                                    ? '<span class="badge badge-primary">Promo</span>'
                                                    : '' !!}
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <a href="{{ url("/landing/service/{$service->id}") }}" class="w-100">
                                                    Selengkapnya
                                                    <i class="fas fa-arrow-right ml-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 py-4">
                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="text-center">
                                        <h1><i class="fa-solid fa-folder-open"></i></h1>

                                        <p class="text-lead">Layanan tidak ditemukan.</p>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white py-5">
                    <div class="container custom-container-section">
                        <h4 class="text-center mb-4">Ulasan Pelanggan</h4>

                        <div id="loading-customer-review" class="text-center py-4">
                            <img src="{{ asset('img/static/spinner-primary.svg') }}" alt="loading"
                                class="d-block mx-auto mb-2">
                            <span>Memuat ulasan...</span>
                        </div>

                        <div class="owl-carousel owl-theme d-none" id="customer-review-carousel">
                            @forelse ($customerReviews as $review)
                                @php
                                    $customer = $review?->reviewingCustomer;
                                    $rating = $review->rating;
                                @endphp
                                <div class="p-4 bg-white rounded border shadow-sm mx-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="" class="rounded-circle mr-3 avatar-initials"
                                            data-name="{{ $customer->fullname ?? 'N/A' }}"
                                            alt="{{ $customer->fullname ?? 'N/A' }}" style="width: 64px; height: 64px;">
                                        <div>
                                            <h6 class="mb-1 font-weight-bold">{{ $customer->fullname ?? 'N/A' }}</h6>
                                            <small class="text-muted" data-toggle="tooltip"
                                                title="{{ carbon_format_date($review->review_at, 'datetime') . " {$zone}" }}">
                                                {{ carbon_format_date($review->review_at, 'human') }}
                                            </small>
                                            <div class="mt-1">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    @if ($i <= $rating)
                                                        <i class="fas fa-star text-warning"></i>
                                                    @else
                                                        <i class="far fa-star text-muted"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    <article class="text-muted" style="font-size: 0.95rem;">
                                        {!! $review->review ?? '-' !!}
                                    </article>
                                </div>
                            @empty
                                <div class="text-center p-4">
                                    <h1><i class="fa-solid fa-comment-dots"></i></h1>

                                    <p class="text-lead">Belum ada ulasan.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="container custom-container-section pt-5 mt-3">
                    <div class="card card-primary">
                        <div class="row m-0">
                            <div class="col-12 col-md-12 col-lg-5 p-0">
                                <div class="card-header text-center">
                                    <h4>Kontak Kami</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-4 col-sm-12">
                                            <div class="media mb-4">
                                                <i class="fa-solid fa-envelope fa-2x mr-3" style="min-width: 2.5rem"></i>
                                                <div class="media-body">
                                                    <h6 class="mt-0 mb-1">Email</h6>
                                                    <p class="mb-0">{{ $site->email }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-4 col-sm-12">
                                            <div class="media mb-4">
                                                <i class="fa-brands fa-whatsapp fa-2x mr-3" style="min-width: 2.5rem"></i>
                                                <div class="media-body">
                                                    <h6 class="mt-0 mb-1">WhatsApp</h6>
                                                    <p class="mb-0">{{ $site->phone_number }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-md-4 col-sm-12">
                                            <div class="media">
                                                <i class="fa-solid fa-map-location-dot fa-2x mr-3"
                                                    style="min-width: 2.5rem"></i>
                                                <div class="media-body">
                                                    <h6 class="mt-0 mb-1">Alamat</h6>
                                                    <p class="mb-0">{{ $site->address }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-12 col-lg-7 p-0">
                                <iframe
                                    src="{{ 'https://maps.google.com/maps?q=' . urlencode($site->address) . '&hl=id&m=h&output=embed' }}"
                                    class="rounded border-0 w-100 h-100" style="min-height: 280px" allowfullscreen=""
                                    loading="lazy"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- JS Libraies -->
    <script src="{{ asset('modules/owlcarousel2/dist/owl.carousel.min.js') }}"></script>

    <!-- Page Specific JS File -->
    <script>
        $(document).ready(function() {
            // Inisialisasi Owl Carousel
            $("#customer-review-carousel").owlCarousel({
                items: 4,
                margin: 20,
                autoplay: true,
                autoplayTimeout: 5000,
                loop: true,
                responsive: {
                    0: {
                        items: 1
                    },
                    578: {
                        items: 2
                    },
                    768: {
                        items: 2
                    },
                    922: {
                        items: 3
                    }
                },
                onInitialized: function() {
                    // Sembunyikan loading dan tampilkan carousel setelah siap
                    $('#loading-customer-review').hide();
                    $('#customer-review-carousel').removeClass('d-none');
                }
            });
        });
    </script>
@endpush

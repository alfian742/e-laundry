@extends('layouts.landing')

@section('title', 'Detail Layanan Laundry')

@push('styles')
@endpush

@section('main')
    <div class="main-content container custom-container">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <div class="card" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1200">
                            <div class="card-body">
                                <div class=" d-flex flex-wrap justify-content-center justify-content-sm-between align-items-center mb-4"
                                    style="gap: .5rem">
                                    <a href="{{ url('/landing/service') }}" class="btn btn-secondary btn-icon icon-left"><i
                                            class="fas fa-arrow-left mr-1"></i> Kembali</a>
                                </div>

                                <div class="row g-4 justify-content-center">
                                    <div class="col-12">
                                        <!-- style sebagai parameter -->
                                        <div class="ratio ratio-4x3 mb-4" style="max-width: 360px; margin: auto;">
                                            <img src="{{ get_image_url($service->img) }}"
                                                alt="{{ $service->service_name ?? 'N/A' }}" loading="eager"
                                                class="rounded d-block w-100 h-100" style="object-fit: cover;">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <h3 class="card-title text-center mb-3">
                                            {{ $service->service_name ?? 'N/A' }}
                                        </h3>

                                        <h5 class="card-subtitle text-center mb-3" style="line-height: 2rem">
                                            {{ formatRupiah($service->price_per_kg) . ' per (Kg/Item)' }}

                                            <span class="d-none d-sm-inline">|</span>

                                            {!! $service->active
                                                ? '<span class="badge badge-success">Layanan Tersedia</span>'
                                                : '<span class="badge badge-danger">Layanan Tidak Tersedia</span>' !!}

                                            <span class="d-none d-sm-inline">|</span>

                                            {!! !$service->promos->isEmpty() && $service->promos->where('active', true)->count() > 0
                                                ? '<a href="#promo-service" class="badge badge-primary scroll-link" data-toggle="tooltip" title="Klik untuk melihat promo">Ada Promo</a>'
                                                : '<span style="font-size: .9rem">Tidak Ada Promo</span>' !!}
                                        </h5>

                                        <div class="text-center">
                                            <span class="text-small">
                                                Layanan Dibuat pada
                                                {{ carbon_format_date($service->created_at, 'datetime') . " $zone" }}
                                            </span>
                                        </div>

                                        <article class="mt-3">
                                            {!! $service->description ?? '' !!}
                                        </article>
                                    </div>

                                    @if (!$service->promos->isEmpty())
                                        <div class="col-12" id="promo-service">
                                            <div class="section-title">Promo untuk layanan ini</div>
                                            <p Class="section-lead mb-4">
                                                <span class="text-danger">*</span> Syarat dan ketentuan berlaku untuk setiap
                                                promo.
                                            </p>

                                            @foreach ($promoService as $promo)
                                                <div
                                                    class="row justify-content-center justify-content-md-between align-items-center border-top m-0 py-3">
                                                    <div class="col-md-2">
                                                        <div class="bg-warning text-white fw-bold py-3 rounded"
                                                            style="width: 6rem">
                                                            <h4
                                                                class="d-flex justify-content-center align-items-center h-100 mb-0">
                                                                {{ $promo->discount_percent ? intval($promo->discount_percent) . '%' : 'N/A' }}
                                                            </h4>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <div class="row justify-content-between">
                                                            <div class="col-sm-8">
                                                                <div class="py-2 py-md-0 h-100">
                                                                    <span class="text-small">
                                                                        @if ($promo->customer_scope === 'member')
                                                                            Member
                                                                        @elseif($promo->customer_scope === 'non_member')
                                                                            Non-Member
                                                                        @else
                                                                            N/A
                                                                        @endif
                                                                    </span>
                                                                    <h6 class="font-weight-bold text-dark mb-0"
                                                                        style="line-height: 1.25rem">
                                                                        {{ $promo->promo_name ?? 'N/A' }} <br>
                                                                        <span class="text-small">
                                                                            @if ($promo->promo_type === 'daily')
                                                                                Berlaku setiap hari
                                                                                @if ($promo->day_of_week === 'monday')
                                                                                    Senin
                                                                                @elseif ($promo->day_of_week === 'tuesday')
                                                                                    Selasa
                                                                                @elseif ($promo->day_of_week === 'wednesday')
                                                                                    Rabu
                                                                                @elseif ($promo->day_of_week === 'thursday')
                                                                                    Kamis
                                                                                @elseif ($promo->day_of_week === 'friday')
                                                                                    Jumat
                                                                                @elseif ($promo->day_of_week === 'saturday')
                                                                                    Sabtu
                                                                                @elseif ($promo->day_of_week === 'sunday')
                                                                                    Minggu
                                                                                @else
                                                                                    N/A
                                                                                @endif
                                                                            @elseif($promo->promo_type === 'date_range')
                                                                                Berlaku pada
                                                                                {{ carbon_format_date($promo->start_date) }}
                                                                                -
                                                                                {{ carbon_format_date($promo->end_date) }}
                                                                            @else
                                                                                N/A
                                                                            @endif
                                                                        </span>
                                                                    </h6>
                                                                    <p class="text-lead mb-0">{{ $promo->description }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div
                                                                    class="d-flex justify-content-start justify-content-sm-end align-items-center h-100">
                                                                    @if ($promo->active)
                                                                        @if ($promo->promo_type === 'date_range' && $promo->end_date < date('Y-m-d'))
                                                                            <span class="badge badge-danger">Masa Berlaku
                                                                                Habis</span>
                                                                        @elseif ($promo->promo_type === 'date_range' && $promo->start_date > date('Y-m-d'))
                                                                            <span class="badge badge-warning">Belum
                                                                                Berlaku</span>
                                                                        @else
                                                                            <span
                                                                                class="badge badge-success">Tersedia</span>
                                                                        @endif
                                                                    @else
                                                                        <span class="badge badge-danger">Tidak
                                                                            Tersedia</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
@endpush

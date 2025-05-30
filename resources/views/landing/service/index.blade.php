@extends('layouts.landing')

@section('title', 'Layanan Laundry')

@push('styles')
@endpush

@section('main')
    <div class="main-content container custom-container">
        <section class="section">
            <div class="section-body custom-mt">
                <div class="row justify-content-center">
                    <div class="col-12 pb-4 pb-lg-2">
                        <form action="{{ route('landing.service.index') }}" method="GET"
                            class="d-flex justify-content-center align-items-center mb-4">
                            <div class="input-group" style="max-width: 460px; width: 100%;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-0">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                                <input type="search" name="search" class="form-control bg-white border-0"
                                    placeholder="Cari layanan..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary custom-border-btn px-4 shadow-none"
                                        type="submit">Cari</button>
                                </div>
                            </div>
                        </form>
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
                                                alt="{{ $service->service_name ?? 'N/A' }}"
                                                loading="{{ $loop->iteration <= 8 ? 'eager' : 'lazy' }}"
                                                class="rounded-top d-block w-100 h-100" style="object-fit: cover;">
                                        </div>
                                    </div>

                                    <div class="p-4">
                                        <h5 class="card-title text-clamp" title="{{ $service->service_name ?? 'N/A' }}">
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
                        <div class="col-12 py-5">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="text-center">
                                    <h1><i class="fa-solid fa-folder-open"></i></h1>

                                    <p class="text-lead">Layanan tidak ditemukan.</p>

                                    <a href="{{ url('/landing/service') }}"><i class="fas fa-arrow-left mr-1"></i> Semua
                                        Layanan</a>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
@endpush

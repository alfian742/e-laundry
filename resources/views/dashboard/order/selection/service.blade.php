@extends('layouts.dashboard')

@section('title', 'Layanan Laundry')

@push('styles')
    <style>
        .item-label {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            border: 1px solid transparent;
            padding: .325rem .25rem;
            font-size: .5rem;
            line-height: 1.5;
            border-radius: .25rem;
            transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out
        }

        .item-label i {
            font-size: 1.5rem;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>@yield('title')</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ url('/dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">@yield('title')</div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-12">
                    <form action="{{ route('order.services') }}" method="GET"
                        class="d-flex justify-content-center align-items-center mb-4">
                        <div class="input-group" style="max-width: 360px; width: 100%;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-0">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="search" name="search" class="form-control bg-white border-0"
                                placeholder="Cari layanan..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary px-4" type="submit">Cari</button>
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

                                    <div class="d-flex justify-content-between align-items-center mb-4" style="gap: .5rem">
                                        <h6 class="mb-0">{{ formatRupiah($service->price_per_kg) }}</h6>
                                        {!! !$service->promos->isEmpty() && $service->promos->where('active', true)->count() > 0
                                            ? '<span class="badge badge-primary">Promo</span>'
                                            : '' !!}
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ url("/service/{$service->id}") }}" class="w-100">
                                            Selengkapnya
                                            <i class="fas fa-arrow-right ml-1"></i>
                                        </a>

                                        @if (in_array($service->id, $addedServiceIds))
                                            <div class="item-label bg-white text-success"
                                                title="Telah ditambahkan ke keranjang">
                                                <i class="fas fa-circle-check"></i>
                                            </div>
                                        @else
                                            <form action="{{ route('order.cart.store', $service->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success" title="Tambah ke keranjang">
                                                    <i class="fas fa-cart-plus"></i>
                                                </button>
                                            </form>
                                        @endif
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

                                <a href="{{ url('/order/services') }}"><i class="fas fa-arrow-left mr-1"></i> Semua
                                    Layanan</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection

@push('scripts')
@endpush

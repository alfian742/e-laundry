@extends('layouts.dashboard')

@section('title', 'Notifikasi')

@push('styles')
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

            <div class="row">
                @if ($all->isEmpty())
                    <div class="col-12 py-5">
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="text-center">
                                <h1><i class="fa-solid fa-bell"></i></h1>

                                <p class="text-lead">Tidak ada notifikasi terbaru.</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-end mb-2" style="gap: .5rem">
                                    @if ($orderCount > 0)
                                        <h6>
                                            Pesanan Baru
                                            <span class="badge badge-primary rounded">{{ $orderCount }}</span>
                                        </h6>
                                    @endif
                                    @if (!$isEmployee)
                                        @if ($reviewCount > 0)
                                            <h6>
                                                Ulasan Pelanggan
                                                <span class="badge badge-secondary rounded">{{ $reviewCount }}</span>
                                            </h6>
                                        @endif
                                        @if ($transactionCount > 0)
                                            <h6>
                                                Pembayaran (Menunggu Konfirmasi)
                                                <span class="badge badge-warning rounded">{{ $transactionCount }}</span>
                                            </h6>
                                        @endif
                                    @endif
                                </div>
                                <ul class="list-group">
                                    @foreach ($all as $notif)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="d-block">
                                                    @if ($notif['type'] === 'order')
                                                        <i class="fas fa-dolly mr-1"></i> Pesanan Baru
                                                    @elseif ($notif['type'] === 'review')
                                                        <i class="fas fa-comment mr-1"></i> Ulasan Pelanggan
                                                    @elseif ($notif['type'] === 'transaction')
                                                        <i class="fas fa-money-bill-transfer mr-1"></i> Pembayaran (Menunggu
                                                        Konfirmasi)
                                                    @endif
                                                </strong>
                                                <span class="d-block">{{ $notif['name'] }}</span>
                                                <small class="text-muted">{{ $notif['time'] }}</small>
                                            </div>
                                            <a href="{{ $notif['url'] }}" class="btn btn-primary">Lihat</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

@push('scripts')
@endpush

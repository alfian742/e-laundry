@extends('layouts.landing')

@section('title', 'Cek Pesanan')

@push('styles')
@endpush

@section('main')
    <div class="main-content container custom-container">
        <section class="section">
            <div class="section-body custom-mt">
                <div class="row justify-content-center">
                    <div class="col-12 pb-4 pb-lg-2">
                        <form action="{{ route('landing.order.check') }}" method="GET"
                            class="d-flex justify-content-center align-items-center mb-4">
                            <div class="input-group" style="max-width: 460px; width: 100%;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-0">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                                <input type="search" name="order_code" class="form-control bg-white border-0"
                                    placeholder="Kode pesanan..." value="{{ request('order_code') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary custom-border-btn px-4 shadow-none"
                                        type="submit">Cek</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if (request('order_code'))
                        @if ($order)
                            <div class="col-12">
                                <div class="card p-0">
                                    <div class="card-body p-4">
                                        <div class=" d-flex justify-content-between align-items-center mb-4"
                                            style="gap: .5rem">
                                            <h5 class="card-subtitle mb-0">Detail Pesanan</h5>
                                        </div>

                                        <div class="table-responsive mb-4">
                                            <table class="table table-sm table-borderless table-custom">
                                                <tr>
                                                    <th>Kode Pesanan</th>
                                                    <td>:</td>
                                                    <td>
                                                        {{ $order->order_code ?? '-' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Dibuat Pada</th>
                                                    <td>:</td>
                                                    <td>
                                                        {{ carbon_format_date($order->created_at, 'datetime') . " {$zone}" }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Metode Antar/Jemput</th>
                                                    <td>:</td>
                                                    <td>
                                                        <div class="text-wrap-overflow">
                                                            {{ $delivery->method_name ?? '-' }}
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Waktu Jemput</th>
                                                    <td>:</td>
                                                    <td>
                                                        {{ carbon_format_date($order->pickup_date) ?? '' }} -
                                                        {{ $order->pickup_time ? carbon_format_date($order->pickup_time, 'time') . " {$zone}" : '' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Waktu Antar</th>
                                                    <td>:</td>
                                                    <td>
                                                        {{ carbon_format_date($order->delivery_date) ?? '' }} -
                                                        {{ $order->delivery_time ? carbon_format_date($order->delivery_time, 'time') . " {$zone}" : '' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><span class="text-danger">*</span> Biaya Antar/Jemput</th>
                                                    <td>:</td>
                                                    <td>
                                                        <div class="text-wrap-overflow">
                                                            {{ formatRupiah($order->delivery_cost) ?? '-' }}
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Status Pesanan</th>
                                                    <td>:</td>
                                                    <td>
                                                        @if ($order->order_status === 'new')
                                                            <span class="badge badge-primary">Baru</span>
                                                        @elseif ($order->order_status === 'pending')
                                                            <span class="badge badge-warning">Menungggu</span>
                                                        @elseif ($order->order_status === 'in_progress')
                                                            <span class="badge badge-info">Diproses</span>
                                                        @elseif ($order->order_status === 'pickup')
                                                            <span class="badge badge-secondary">Dijemput</span>
                                                        @elseif ($order->order_status === 'delivery')
                                                            <span class="badge badge-secondary">Diantar</span>
                                                        @elseif ($order->order_status === 'done')
                                                            <span class="badge badge-success">Selesai</span>
                                                        @elseif ($order->order_status === 'canceled')
                                                            <span class="badge badge-danger">Dibatalkan</span>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Status Pembayaran</th>
                                                    <td>:</td>
                                                    <td>
                                                        @if ($order->payment_status === 'unpaid')
                                                            <span class="badge badge-danger">Belum Dibayar</span>
                                                        @elseif ($order->payment_status === 'partial')
                                                            <span class="badge badge-warning">Belum Lunas</span>
                                                        @elseif ($order->payment_status === 'paid')
                                                            <span class="badge badge-success">Lunas</span>
                                                        @else
                                                            N/A
                                                        @endif

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Catatan</th>
                                                    <td>:</td>
                                                    <td>
                                                        <div class="text-wrap-overflow">
                                                            {{ $order->notes ?? '-' }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                        <div class="table-responsive mb-4">
                                            <table class="table table-sm table-bordered table-nowrap table-align-middle">
                                                <thead class="bg-light text-center">
                                                    <tr>
                                                        <th rowspan="2">Nama Layanan</th>
                                                        <th rowspan="2">Harga per (Kg/Item)</th>
                                                        <th rowspan="2">Berat (Kg)/Jumlah (Item)</th>
                                                        <th rowspan="2">Subtotal</th>
                                                        <th rowspan="2">Promo</th>
                                                        <th colspan="2">
                                                            <span class="text-danger">*</span> Diskon
                                                        </th>
                                                        <th rowspan="2">
                                                            Total
                                                            <br>
                                                            (Subtotal - Diskon)
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <th>(%)</th>
                                                        <th>(Rp)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $details = $order?->orderDetails;
                                                        $finalServicePrice = 0;
                                                    @endphp

                                                    @foreach ($details as $detail)
                                                        @php
                                                            $service = $detail?->includedService;
                                                            $promo = $detail?->includedPromo;
                                                        @endphp

                                                        <tr>
                                                            <td>
                                                                {{ $service->service_name ?? '-' }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ formatRupiah($service->price_per_kg) ?? '-' }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ $detail->weight_kg ? intval($detail->weight_kg) : '-' }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ formatRupiah($detail->total_price) ?? '-' }}
                                                            </td>
                                                            <td>
                                                                {{ $promo->promo_name ?? '-' }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ $detail->discount_percent ? intval($detail->discount_percent) . '%' : '-' }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ formatRupiah($detail->total_price * ($detail->discount_percent / 100)) ?? '-' }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ formatRupiah($detail->final_service_price) ?? '-' }}
                                                            </td>
                                                        </tr>
                                                        @php $finalServicePrice += $detail->final_service_price; @endphp
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="bg-light">
                                                    <tr>
                                                        <td colspan="7" class="text-center font-weight-bold">Total
                                                            Harga Pesanan</td>
                                                        <td class="text-right font-weight-bold">
                                                            {{ formatRupiah($finalServicePrice) ?? '-' }}
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <p class="text-lead mb-4">
                                            <strong><span class="text-danger">*</span> Biaya Antar/Jemput</strong> dan
                                            <strong><span class="text-danger">*</span> Diskon</strong>
                                            hanya berlaku ketika pesanan ini dibuat.
                                        </p>

                                        @php
                                            $hasUnweighedItem = collect($details)->contains(function ($item) {
                                                return $item->weight_kg == 0; // loose comparison: aman di kasus ini
                                            });
                                        @endphp

                                        <div class="table-responsive">
                                            <table class="table table-sm table-borderless table-custom">
                                                <tr>
                                                    <th>Total Pembayaran</th>
                                                    <td>=</td>
                                                    <td>
                                                        Total Harga Pesanan + Biaya Antar/Jemput
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Total Pembayaran</th>
                                                    <td>=</td>
                                                    <td>
                                                        {{ formatRupiah($finalServicePrice) ?? '-' }}
                                                        +
                                                        {{ formatRupiah($order->delivery_cost) ?? '-' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Total Pembayaran</th>
                                                    <td>=</td>
                                                    <td>
                                                        @php $finalAmountPaid = $finalServicePrice + $order->delivery_cost @endphp

                                                        {{ formatRupiah($finalAmountPaid) ?? '-' }}

                                                        @if ($order->order_status !== 'canceled')
                                                            {!! !$hasUnweighedItem && count($details) > 0
                                                                ? ''
                                                                : '<span class="font-weight-bold text-danger">(Belum dikonfirmasi)</span>' !!}
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="col-12 py-5">
                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="text-center">
                                        <h1><i class="fa-solid fa-folder-open"></i></h1>

                                        <p class="text-lead">
                                            Pesanan tidak ditemukan. <br> Silakan periksa kembali kode pesanan yang Anda
                                            masukkan.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="col-12">
                            <div class="d-flex justify-content-center">
                                <div class="alert alert-light" role="alert" style="max-width: 460px; width: 100%;">
                                    <h6>Panduan</h6>
                                    <p class="text-lead mb-0">
                                        Silakan pindai kode QR yang tertera pada struk pesanan atau masukkan kode pesanan
                                        pada kolom pencarian yang tersedia.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
@endpush

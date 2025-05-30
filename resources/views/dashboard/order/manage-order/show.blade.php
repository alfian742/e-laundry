@extends('layouts.dashboard')

@section('title', 'Detail Pesanan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/chocolat/dist/css/chocolat.css') }}">
    <style>
        .table-custom {
            white-space: nowrap !important;
        }


        .table-custom tr th td {
            vertical-align: middle !important;
        }

        .table-custom tr th {
            width: 10rem !important;
        }

        .table-custom tr td:nth-child(2) {
            width: 1rem !important;
        }

        .chocolat-content {
            position: fixed;
            width: auto !important;
            height: auto !important;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .chocolat-content .chocolat-img {
            max-width: 90vw;
            max-height: 90vh;
            width: auto !important;
            height: auto !important;
            object-fit: contain;
        }

        .chocolat-overlay {
            background-color: rgba(0, 0, 0, 0.75) !important;
        }

        .chocolat-loader {
            background: url('{{ asset('img/static/spinner-primary.svg') }}') !important;
        }

        .chocolat-overlay,
        .chocolat-loader,
        .chocolat-wrapper,
        .chocolat-content {
            z-index: 99999 !important;
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
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class=" d-flex flex-wrap justify-content-center justify-content-sm-between align-items-center mb-4"
                                style="gap: .5rem">
                                <a href="{{ Str::contains(url()->previous(), url('/pickup-delivery-schedule')) ||
                                (Str::contains(url()->previous(), '/order/') && Str::contains(url()->previous(), '/transaction')) ||
                                Str::contains(url()->previous(), url('/revenue'))
                                    ? 'javascript:history.back()'
                                    : url('/order') }}"
                                    class="btn btn-secondary">Kembali</a>

                                <div class="d-flex flex-wrap justify-content-center align-items-center" style="gap: .5rem">
                                    @if (!$isCustomer)
                                        @if (!$details->isEmpty())
                                            <button type="button" data-url="{{ url("/order/{$order->id}/print-detail") }}"
                                                class="btn btn-success btn-print-page">
                                                <i class="fas fa-print mr-1"></i> Cetak Detail Pesanan
                                            </button>
                                        @endif

                                        @if (!$details->isEmpty() && !$transactions->isEmpty())
                                            <button type="button" data-url="{{ url("/order/{$order->id}/print-receipt") }}"
                                                class="btn btn-success btn-print-page">
                                                <i class="fas fa-print mr-1"></i> Cetak Struk Pembayaran
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="row g-4 justify-content-center">
                                <div class="col-12">
                                    <h5 class="card-title mb-4">Data Pelanggan</h5>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless table-custom">
                                            <tr>
                                                <th>Nama Lengkap</th>
                                                <td>:</td>
                                                <td>{{ $customer->fullname ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Tipe Pelanggan</th>
                                                <td>:</td>
                                                <td>
                                                    @if ($customer->customer_type === 'member')
                                                        Member
                                                    @elseif($customer->customer_type === 'non_member')
                                                        Non-Member
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Nomor HP/WA</th>
                                                <td>:</td>
                                                <td>
                                                    @if (!$isCustomer)
                                                        <a href="https://wa.me/{{ formatPhoneNumber($customer->phone_number) }}"
                                                            target="_blank">
                                                            {{ $customer->phone_number ?? '-' }}
                                                            </button>
                                                        @else
                                                            {{ $customer->phone_number ?? '-' }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Alamat</th>
                                                <td>:</td>
                                                <td>
                                                    <div class="text-wrap-overflow">
                                                        {{ $customer->address ?? '-' }}
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                @if (!$details->isEmpty())
                                    <div class="col-12">
                                        <hr class="my-4">
                                    </div>

                                    <div class="col-12">
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
                                                    @php $finalServicePrice = 0 @endphp
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
                                @endif

                                @if (!$transactions->isEmpty())
                                    <div class="col-12">
                                        <hr class="my-4">
                                    </div>

                                    <div class="col-12">
                                        <div class=" d-flex justify-content-between align-items-center mb-4"
                                            style="gap: .5rem">
                                            <h5 class="card-subtitle mb-0">Detail Pembayaran</h5>
                                        </div>

                                        <div class="table-responsive mb-4">
                                            <table class="table table-sm table-bordered table-align-middle table-nowrap">
                                                <thead class="bg-light">
                                                    <tr class="text-center">
                                                        <th>Waktu Pembayaran</th>
                                                        <th>Kode Pembayaran</th>
                                                        <th>Metode Pembayaran</th>
                                                        <th>Status</th>
                                                        <th>Keterangan</th>
                                                        <th>Bukti Pembayaran</th>
                                                        <th>Jumlah Pembayaran</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $totalAmountPaid = 0 @endphp
                                                    @foreach ($transactions as $transaction)
                                                        @php
                                                            $paymentMethod = $transaction?->usedPaymentMethod;
                                                            $proof = $transaction?->relatedProof;
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                {{ $transaction->paid_at ? carbon_format_date($transaction->paid_at, 'datetime') . " {$zone}" : '-' }}
                                                            </td>
                                                            <td>
                                                                @if ($transaction->invoice_id)
                                                                    <div class="clipboard d-flex justify-content-between align-items-center"
                                                                        style="gap: .5rem">
                                                                        <div class="clipboard-text">
                                                                            {{ $transaction->invoice_id }}
                                                                        </div>
                                                                        <button class="btn btn-clipboard" type="button"
                                                                            data-toggle="tooltip"
                                                                            title="Salin Kode Pembayaran"
                                                                            onclick="copyToClipboard(event)">
                                                                            <i class="fas fa-clipboard"></i>
                                                                        </button>
                                                                    </div>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="text-wrap-overflow">
                                                                    {{ $paymentMethod->method_name ?? '-' }}
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($transaction->status === 'success')
                                                                    <span class="badge badge-success">Sukses</span>
                                                                @elseif ($transaction->status === 'failed')
                                                                    <span class="badge badge-danger">Gagal</span>
                                                                @elseif ($transaction->status === 'rejected')
                                                                    <span class="badge badge-danger">Ditolak</span>
                                                                @elseif ($transaction->status === 'pending')
                                                                    <span class="badge badge-warning">Menunggu</span>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="text-wrap-overflow">
                                                                    {{ $transaction->notes ?? '-' }}
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                @if (in_array($paymentMethod->payment_type, ['online', 'bank_transfer']) && $proof?->img !== null)
                                                                    <div class="chocolat-parent">
                                                                        <a href="{{ get_image_url($proof?->img) }}"
                                                                            class="chocolat-image">
                                                                            Lihat <i
                                                                                class="fas fa-up-right-from-square ml-1"></i>
                                                                        </a>
                                                                    </div>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td class="text-right">
                                                                {{ formatRupiah($transaction->amount_paid) }}
                                                            </td>
                                                        </tr>
                                                        @php $totalAmountPaid += $transaction->amount_paid @endphp
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tfoot class="bg-light">
                                                        <tr>
                                                            <td colspan="6" class="text-center font-weight-bold">
                                                                Total Jumlah Pembayaran</td>
                                                            <td class="text-right font-weight-bold">
                                                                {{ formatRupiah($totalAmountPaid) ?? '-' }}
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-sm table-borderless table-custom">
                                                <tr>
                                                    <th>Total Pembayaran</th>
                                                    <td>=</td>
                                                    <td>
                                                        {{ formatRupiah($finalAmountPaid) ?? '-' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Total Jumlah Pembayaran</th>
                                                    <td>=</td>
                                                    <td>
                                                        {{ formatRupiah($totalAmountPaid) ?? '-' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    @php
                                                        $remainingPayment = $finalAmountPaid - $totalAmountPaid;
                                                    @endphp

                                                    @if ($remainingPayment < 0)
                                                        <th>Uang Kembali</th>
                                                        <td>=</td>
                                                        <td>{{ formatRupiah(abs($remainingPayment)) }}</td>
                                                    @else
                                                        <th>Sisa Pembayaran</th>
                                                        <td>=</td>
                                                        <td>{{ formatRupiah($remainingPayment) }}</td>
                                                    @endif
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- iframe print -->
    <iframe id="printPage" class="d-none"></iframe>
@endsection

@push('scripts')
    <script src="{{ asset('modules/chocolat/dist/js/jquery.chocolat.min.js') }}"></script>
    @if (!$isCustomer && !$details->isEmpty())
        <!-- Print -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const printButtons = document.querySelectorAll('.btn-print-page');
                const printFrame = document.getElementById('printPage');

                printButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const url = this.getAttribute('data-url');

                        printFrame.onload = function() {
                            try {
                                printFrame.contentWindow.focus();
                                printFrame.contentWindow.print();
                            } catch (e) {
                                swal({
                                    title: 'Gagal Mencetak',
                                    text: 'Tidak dapat memuat data',
                                    icon: 'error',
                                    button: 'OK',
                                    timer: 5000
                                });
                            }
                        };

                        setTimeout(() => {
                            printFrame.src = url;
                        }, 100);
                    });
                });
            });
        </script>
    @endif
@endpush

@extends('layouts.print')

@section('title', 'Cetak Struk Pesanan')

@push('styles')
    <style>
        @media print {
            @page {
                size: 80mm auto !important;
                margin: 0;
            }
        }

        .table-custom tr th {
            width: 10rem !important;
        }

        .table-custom tr td:nth-child(2) {
            width: 1rem !important;
        }

        .table-head-middle-center thead tr th {
            vertical-align: middle !important;
            text-align: center !important;
        }

        .order-detail-wrapper {
            position: relative;
        }

        .qrcode-container {
            position: absolute;
            top: 0;
            right: 0;
            background: white;
            z-index: 9999;
        }
    </style>
@endpush

@section('main')
    <div class="row g-4 justify-content-center">
        <div class="col-12">
            <div class="order-detail-wrapper">
                <div class="qrcode-container">
                    <div id="qrCode"></div>
                    <h6 class="mb-0 mt-2 font-weight-bold text-center">Pindai disini</h6>
                </div>

                <table class="table w-100 table-sm table-borderless table-custom mb-4">
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
                            {{ $customer->phone_number ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td>:</td>
                        <td>
                            {{ $customer->address ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Metode Antar/Jemput</th>
                        <td>:</td>
                        <td>
                            {{ $delivery->method_name ?? '-' }}
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
                            {{ formatRupiah($delivery->cost) ?? '-' }}
                        </td>
                    </tr>
                </table>

                <table class="table w-100 table-sm table-bordered table-head-middle-center mb-4">
                    <thead class="text-center">
                        <tr>
                            <th rowspan="2">Nama Layanan</th>
                            <th rowspan="2">Harga per (Kg/Item)</th>
                            <th rowspan="2">Berat (Kg)/<br>Jumlah (Item)</th>
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
                        @php $finalServicePrice = 0; @endphp
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
                    <tfoot>
                        <tr>
                            <td colspan="7" class="text-center font-weight-bold">Total
                                Harga Pesanan</td>
                            <td class="text-right font-weight-bold">
                                {{ formatRupiah($finalServicePrice) ?? '-' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <p class="text-lead mb-4">
                    <strong><span class="text-danger">*</span> Biaya Antar/Jemput</strong> dan
                    <strong><span class="text-danger">*</span> Diskon</strong>
                    hanya berlaku ketika pesanan ini dibuat.
                </p>

                <table class="table w-100 table-sm table-borderless table-custom mb-4">
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
                            {{ formatRupiah($finalServicePrice) ?? '-' }} +
                            {{ formatRupiah($delivery->cost) ?? '-' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Total Pembayaran</th>
                        <td>=</td>
                        <td>
                            @php $finalAmountPaid = $finalServicePrice + $delivery->cost @endphp
                            {{ formatRupiah($finalAmountPaid) ?? '-' }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="col-12">
            <table class="table w-100 table-sm table-bordered table-head-middle-center table-nowrap mb-4">
                <thead>
                    <tr class="text-center">
                        <th>Waktu Pembayaran</th>
                        <th>Metode Pembayaran</th>
                        <th>Jumlah Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmountPaid = 0; @endphp
                    @foreach ($transactions as $transaction)
                        <tr>
                            <td>
                                {{ $transaction->paid_at ? carbon_format_date($transaction->paid_at, 'datetime') . " {$zone}" : '-' }}
                            </td>
                            <td>{{ $transaction->usedPaymentMethod->method_name ?? '-' }}
                            </td>
                            <td class="text-right">
                                {{ formatRupiah($transaction->amount_paid) }}
                            </td>
                        </tr>
                        @php $totalAmountPaid += $transaction->amount_paid @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-center font-weight-bold">
                                Total Jumlah Pembayaran</td>
                            <td class="text-right font-weight-bold">
                                {{ formatRupiah($totalAmountPaid) ?? '-' }}
                            </td>
                        </tr>
                    </tfoot>
                </tfoot>
            </table>

            <table class="table w-100 table-sm table-borderless table-custom mb-4">
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

        <div class="col-12">
            <hr class="my-4">
            <h6 class="text-center mb-2">TERIMAKASIH TELAH MENGGUNAKAN JASA KAMI</h6>
            <p class="text-center text-lead font-italic mb-2">{{ $site->site_name }} <span class="bullet"></span>
                {{ $site->tagline }}</p>
            <p class="text-center text-lead">{{ $site->address ?? '' }}</p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('modules/qrcodejs/qrcode.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var qrcode = new QRCode(document.getElementById("qrCode"), {
                text: '{{ url("/landing/check-order?order_code={$order->order_code}") }}',
                width: 120,
                height: 120,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });
    </script>
@endpush

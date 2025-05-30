@extends('layouts.print')

@section('title', 'Laporan Pendapatan')

@push('styles')
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }

            body {
                writing-mode: horizontal-tb;
            }
        }
    </style>
@endpush

@section('main')
    <div class="row g-4 justify-content-center">
        <div class="col-12">
            <div class="text-center mb-4">
                <h4>@yield('title')</h4>

                <p class="mb-0">Periode:
                    {{ carbon_format_date($early_period, 'date') }} -
                    {{ carbon_format_date($final_period, 'date') }}
            </div>

            <table class="table w-100 table-sm table-bordered table-head-middle-center mb-4">
                <thead>
                    <tr class="text-center">
                        <th>No.</th>
                        <th>Waktu Pembayaran</th>
                        <th>Kode Pembayaran</th>
                        <th>Kode Pesanan</th>
                        <th>Metode Pembayaran</th>
                        <th>Status</th>
                        <th>Jumlah Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmountPaid = 0 @endphp
                    @forelse ($transactions as $transaction)
                        @php
                            $paymentMethod = $transaction?->usedPaymentMethod;
                            $order = $transaction?->relatedOrder;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $transaction->paid_at ? carbon_format_date($transaction->paid_at, 'datetime') . " {$zone}" : '-' }}
                            </td>
                            <td>
                                {{ $transaction->invoice_id ?? '-' }}
                            </td>
                            <td>
                                {{ $order->order_code }}
                            </td>
                            <td>
                                {{ $paymentMethod->method_name ?? '-' }}
                            </td>
                            <td class="text-center">
                                @if ($transaction->status === 'success')
                                    Sukses
                                @elseif ($transaction->status === 'failed')
                                    Gagal
                                @elseif ($transaction->status === 'rejected')
                                    Ditolak
                                @elseif ($transaction->status === 'pending')
                                    Menunggu
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right">
                                {{ formatRupiah($transaction->amount_paid) }}
                            </td>
                        </tr>
                        @php $totalAmountPaid += $transaction->amount_paid @endphp
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-2">
                                Tidak ada data tersedia di tabel
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-center font-weight-bold">
                                Total Pendapatan</td>
                            <td class="text-right font-weight-bold">
                                {{ formatRupiah($totalAmountPaid) ?? '-' }}
                            </td>
                        </tr>
                    </tfoot>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
@endpush

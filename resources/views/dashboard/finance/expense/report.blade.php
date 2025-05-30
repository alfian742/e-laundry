@extends('layouts.print')

@section('title', 'Laporan Pengeluaran')

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

        .table-head-middle-center thead tr th {
            vertical-align: middle !important;
            text-align: center !important;
        }

        .table-foot-middle-center tfoot tr td {
            vertical-align: middle !important;
        }

        .payment-column {
            min-width: 9rem !important;
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

            <table class="table w-100 table-sm table-bordered table-head-middle-center table-foot-middle-center mb-4">
                <thead>
                    <tr class="text-center">
                        <th>No.</th>
                        <th>Tanggal Pembayaran</th>
                        <th>Jenis Pengeluaran</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th class="payment-column">Total Tagihan</th>
                        <th class="payment-column">Jumlah Pembayaran</th>
                        <th class="payment-column">Sisa Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalAmount = 0;
                        $totalPaidAmount = 0;
                        $totalOutstandingAmount = 0;
                    @endphp
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $expense->paid_at ? carbon_format_date($expense->paid_at, 'date') : '-' }}
                            </td>
                            <td>
                                {{ $expense->expense_category ?? '-' }}
                            </td>
                            <td>
                                {{ $expense->notes ?? '-' }}
                            </td>
                            <td class="text-center">
                                @if ($expense->status === 'unpaid')
                                    Belum Dibayar
                                @elseif ($expense->status === 'partial')
                                    Belum Lunas
                                @elseif ($expense->status === 'paid')
                                    Lunas
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right payment-column">
                                {{ formatRupiah($expense->total_amount) }}
                            </td>
                            <td class="text-right payment-column">
                                {{ formatRupiah($expense->paid_amount) }}
                            </td>
                            <td class="text-right payment-column">
                                {{ formatRupiah($expense->outstanding_amount) }}
                            </td>
                        </tr>
                        @php
                            $totalAmount += $expense->total_amount;
                            $totalPaidAmount += $expense->paid_amount;
                            $totalOutstandingAmount += $expense->outstanding_amount;
                        @endphp
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-2">
                                Tidak ada data tersedia di tabel
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-center font-weight-bold">
                            Total</td>
                        <td class="text-right font-weight-bold payment-column">
                            {{ formatRupiah($totalAmount) ?? '-' }}
                        </td>
                        <td class="text-right font-weight-bold payment-column">
                            {{ formatRupiah($totalPaidAmount) ?? '-' }}
                        </td>
                        <td class="text-right font-weight-bold payment-column">
                            {{ formatRupiah($totalOutstandingAmount) ?? '-' }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
@endpush

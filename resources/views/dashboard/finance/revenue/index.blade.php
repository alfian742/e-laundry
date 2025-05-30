@extends('layouts.dashboard')

@section('title', 'Pendapatan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/select2/dist/css/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/datatables/dataTables.min.css') }}">

    <style>
        .select2-container {
            display: inline-block !important;
            width: auto !important;
            vertical-align: middle;
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
                            <div class=" d-flex flex-wrap justify-content-between align-items-center mb-4"
                                style="gap: .5rem">
                                <form method="GET" action="{{ route('revenue.index') }}" style="width: 27.5rem">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">Saring</span>
                                        </div>

                                        {{-- Dropdown Tahun --}}
                                        <label class="d-none" for="year">&nbsp;</label>
                                        <select class="custom-select select2" name="year" id="year">
                                            @foreach ($availableYears as $year)
                                                <option value="{{ $year }}"
                                                    {{ $year == $selectedYear ? 'selected' : '' }}>
                                                    {{ $year }}
                                                </option>
                                            @endforeach
                                        </select>

                                        {{-- Dropdown Bulan --}}
                                        <label class="d-none" for="month">&nbsp;</label>
                                        <select class="custom-select select2" name="month" id="month">
                                            @foreach ($monthLabels as $key => $label)
                                                @if ($availableMonths->contains($key))
                                                    <option value="{{ $key }}"
                                                        {{ $selectedMonth == $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>

                                        {{-- Tombol Submit --}}
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">Tampilkan</button>
                                        </div>
                                    </div>
                                </form>

                                @if (!$transactions->isEmpty())
                                    <button type="button" data-toggle="modal" data-target="#reportModal"
                                        class="btn btn-success">
                                        <i class="fas fa-print mr-1"></i> Cetak Laporan
                                    </button>
                                @endif
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-striped table-nowrap table-align-middle" id="table-1">
                                    <thead>
                                        <tr class="text-center">
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
                                        @foreach ($transactions as $transaction)
                                            @php
                                                $paymentMethod = $transaction?->usedPaymentMethod;
                                                $order = $transaction?->relatedOrder;
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
                                                                data-toggle="tooltip" title="Salin Kode Pembayaran"
                                                                onclick="copyToClipboard(event)">
                                                                <i class="fas fa-clipboard"></i>
                                                            </button>
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ url("/order/{$order->id}") }}">
                                                        {{ $order->order_code }}
                                                    </a>
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
                                                <td class="text-right">
                                                    {{ formatRupiah($transaction->amount_paid) }}
                                                </td>

                                            </tr>
                                            @php $totalAmountPaid += $transaction->amount_paid @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="p-2">
                                <div class="row justify-content-end align-items-center">
                                    <div class="col-md-6">
                                        <div class="row align-items-center font-weight-bold" style="font-size: 1rem">
                                            <div class="col-sm-5 py-2 border bg-light">
                                                Total Pendapatan
                                            </div>
                                            <div class="col-sm-7 py-2 border text-left text-sm-right">
                                                {{ formatRupiah($totalAmountPaid) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @if (!$transactions->isEmpty())
        <!-- Modal cetak laporan -->
        <div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form class="modal-content" action="{{ route('revenue.report') }}" method="GET" target="printReportFrame"
                    onsubmit="handlePrintReport();">
                    <div class="modal-body p-3">
                        <div class="d-flex justify-content-between align-items-center" style="gap: .5rem">
                            <h5 class="modal-title mb-0" id="reportModalLabel">Laporan @yield('title')</h5>
                            <button type="button" class="px-2 py-1 close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <hr class="mt-3 mb-4">

                        <div class="row g-4">
                            <div class="form-group col-md-6">
                                <label for="early_period">Periode Awal <span class="text-danger">*</span></label>
                                <input id="early_period" type="date"
                                    class="form-control @error('early_period') is-invalid @enderror" name="early_period"
                                    value="{{ old('early_period') }}" required>
                                <div class="invalid-feedback">
                                    @error('early_period')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="final_period">Periode Akhir <span class="text-danger">*</span></label>
                                <input id="final_period" type="date"
                                    class="form-control @error('final_period') is-invalid @enderror" name="final_period"
                                    value="{{ old('final_period') }}" required>
                                <div class="invalid-feedback" id="final-period-error">
                                    @error('final_period')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end align-items-center" style="gap: .5rem">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-print mr-1"></i> Cetak
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- iframe print report -->
        <iframe id="printReportFrame" name="printReportFrame" class="d-none"></iframe>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('modules/datatables/dataTables.min.js') }}"></script>
    <script src="{{ asset('js/page/modules-datatables.js') }}"></script>
    <script src="{{ asset('modules/select2/dist/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#month, #year').select2({
                theme: 'bootstrap4',
            });

            // Saat tahun berubah, load ulang bulan
            $('#year').on('change', function() {
                var selectedYear = $(this).val();

                // Kosongkan dropdown bulan
                // Ditambah: tampilkan "Memuat..." sementara
                $('#month').empty().append(
                    new Option('Memuat...', '', true, true)
                ).trigger('change');

                // AJAX untuk ambil bulan berdasarkan tahun
                $.ajax({
                    url: '{{ route('revenue.get.available.months') }}',
                    data: {
                        year: selectedYear
                    },
                    success: function(data) {
                        // Kosongkan kembali sebelum menambahkan opsi hasil dari AJAX
                        $('#month').empty();

                        var options = data.map(function(month) {
                            return new Option(month.text, month.id, false, false);
                        });

                        $('#month').append(options).trigger('change');
                    }
                });
            });
        });
    </script>

    @if (!$transactions->isEmpty())
        <script>
            function validatePeriod() {
                const startInput = document.getElementById('early_period');
                const endInput = document.getElementById('final_period');
                const errorDiv = document.getElementById('final-period-error');
                const submitBtn = document.querySelector('#reportModal button[type="submit"]');

                const startDate = new Date(startInput.value);
                const endDate = new Date(endInput.value);

                // Reset error dan validasi
                errorDiv.textContent = '';
                endInput.classList.remove('is-invalid');
                submitBtn.disabled = false;

                // Cek apakah tanggal valid dan akhir < awal
                if (endInput.value && startInput.value && endDate < startDate) {
                    errorDiv.innerText = 'Periode akhir harus sama atau setelah periode awal.';
                    endInput.classList.add('is-invalid');
                    submitBtn.disabled = true;
                }
            }

            // Pasang event listener
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('early_period').addEventListener('input', validatePeriod);
                document.getElementById('final_period').addEventListener('input', validatePeriod);
            });

            function handlePrintReport() {
                var printReportFrame = document.getElementById('printReportFrame');

                printReportFrame.onload = function() {
                    printReportFrame.contentWindow.print();
                };
            }
        </script>
    @endif
@endpush

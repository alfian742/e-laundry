@extends('layouts.dashboard')

@section('title', 'Kelola Pembayaran')

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
                            <div class=" d-flexjustify-content-between align-items-center mb-4" style="gap: .5rem">
                                <a href="{{ url('/order') }}" class="btn btn-secondary">Kembali</a>
                            </div>

                            @php
                                $hasUnweighedItem = collect($details)->contains(function ($item) {
                                    return $item->weight_kg == 0; // loose comparison: aman di kasus ini
                                });
                            @endphp

                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-borderless table-custom">
                                    <tr>
                                        <th>Kode Pesanan</th>
                                        <td>:</td>
                                        <td>
                                            <a href="{{ url("/order/{$order->id}") }}">
                                                {{ $order->order_code ?? '-' }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Nama Lengkap</th>
                                        <td>:</td>
                                        <td>{{ $customer->fullname ?? '-' }}
                                            ({{ $customer->customer_type === 'member' ? 'Member' : 'Non-Member' }})</td>
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
                                    @if ($isCustomer)
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
                                    @endif
                                </table>
                            </div>

                            <hr class="my-4">

                            <div class=" d-flex flex-wrap justify-content-between align-items-center mb-4"
                                style="gap: .5rem">
                                @if (!$isCustomer && !$transactions->isEmpty())
                                    <form method="POST" action="{{ route('order.update.status.payment', $order->id) }}"
                                        style="width: 20rem">
                                        @csrf
                                        @method('put')
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <label class="input-group-text bg-light"
                                                    for="payment_status">Pembayaran</label>
                                            </div>
                                            <select class="custom-select" name="payment_status" id="payment_status">
                                                <option value="unpaid"
                                                    {{ $order->payment_status === 'unpaid' ? 'selected' : '' }}>
                                                    Belum Dibayar
                                                </option>
                                                <option value="partial"
                                                    {{ $order->payment_status === 'partial' ? 'selected' : '' }}>
                                                    Belum Lunas
                                                </option>
                                                <option value="paid"
                                                    {{ $order->payment_status === 'paid' ? 'selected' : '' }}>
                                                    Lunas
                                                </option>
                                            </select>
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                            </div>
                                        </div>
                                    </form>
                                @elseif(!$isCustomer && $transactions->isEmpty())
                                    @php
                                        // Pesan untuk transaksi yang masih kosong
                                        $customerData = $order?->orderingCustomer;

                                        $fullname = $customerData->fullname ?? 'N/A';
                                        $phone_number = $customerData->phone_number
                                            ? formatPhoneNumber($customerData->phone_number)
                                            : 'N/A';

                                        $messageText =
                                            'Pembayaran belum diterima. Silakan lakukan pembayaran untuk memproses pesanan Anda.';

                                        $message = "Hai {$fullname}, {$messageText}";
                                        $messageUrl = "https://wa.me/{$phone_number}?text=" . urlencode($message);
                                    @endphp

                                    <a href="{{ $messageUrl }}" class="btn btn-success" target="_blank">
                                        Kirim Pesan <i class="fab fa-whatsapp ml-1"></i>
                                    </a>
                                @endif

                                <div class="ml-auto">
                                    @if (!$isCustomer && !$details->isEmpty() && !$transactions->isEmpty())
                                        <button type="button" data-url="{{ url("/order/{$order->id}/print-receipt") }}"
                                            class="btn btn-success btn-print-page">
                                            <i class="fas fa-print mr-1"></i> Cetak Struk Pembayaran
                                        </button>
                                    @endif

                                    @if (!$hasUnweighedItem && count($details) > 0 && $order->payment_status !== 'paid')
                                        @if ($isOwner || $isAdmin || ($isCustomer && !in_array($order->order_status, ['new', 'done', 'canceled'])))
                                            <a href="{{ url("/order/{$order->id}/transaction/create") }}"
                                                class="btn btn-primary ml-1">
                                                Tambah
                                            </a>
                                        @endif
                                    @endif
                                </div>
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
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalAmountPaid = 0 @endphp
                                        @forelse ($transactions as $transaction)
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
                                                                Lihat <i class="fas fa-up-right-from-square ml-1"></i>
                                                            </a>
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    {{ formatRupiah($transaction->amount_paid) }}
                                                </td>
                                                <td class="text-center" style="min-width: 7.5rem">
                                                    <div class="d-flex justify-content-center" style="gap: .5rem">
                                                        @if ($order->payment_status !== 'paid')
                                                            @if (
                                                                ($isOwner || $isAdmin || ($isCustomer && in_array($transaction->status, ['pending', 'rejected']))) &&
                                                                    $paymentMethod->payment_type !== 'midtrans')
                                                                <a href="{{ url("/order/{$order->id}/transaction/{$transaction->id}/edit") }}"
                                                                    class="btn btn-primary" data-toggle="tooltip"
                                                                    title="Ubah Pembayaran">
                                                                    <i class="fas fa-pencil-alt"></i>
                                                                </a>

                                                                @if (($isOwner || $isAdmin) && !in_array($transaction->status, ['success', 'failed']))
                                                                    <form
                                                                        action="{{ route('transaction.order.destroy', ['order' => $order->id, 'transaction' => $transaction->id]) }}"
                                                                        method="POST" id="delete-form-{{ $order->id }}"
                                                                        class="d-inline">
                                                                        @csrf
                                                                        @method('delete')
                                                                        <button type="submit"
                                                                            class="btn btn-danger btn-delete"
                                                                            data-toggle="tooltip"
                                                                            title="Hapus Pembayaran">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        @else
                                                            -
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @php $totalAmountPaid += $transaction->amount_paid @endphp
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-3">
                                                    Tidak ada data tersedia di tabel
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tfoot class="bg-light">
                                            <tr>
                                                <td colspan="6" class="text-center font-weight-bold">
                                                    Total Jumlah Pembayaran</td>
                                                <td class="text-right font-weight-bold">
                                                    {{ formatRupiah($totalAmountPaid) ?? '-' }}
                                                </td>
                                                <td>&nbsp;</td>
                                            </tr>
                                        </tfoot>
                                    </tfoot>
                                </table>
                            </div>

                            @php
                                $finalServicePrice = 0;

                                foreach ($details as $detail) {
                                    $finalServicePrice += $detail->final_service_price;
                                }
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
                                        <th>&nbsp;</th>
                                        <td>=</td>
                                        <td>
                                            {{ formatRupiah($finalServicePrice) ?? '-' }}
                                            +
                                            {{ formatRupiah($order->delivery_cost) ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>&nbsp;</th>
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
    @if (!$isCustomer && !$details->isEmpty() && !$transactions->isEmpty())
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

        @if ($isOwner || $isAdmin)
            <script>
                $(document).ready(function() {
                    // Gunakan delegasi untuk tombol hapus
                    $(document).on('click', '.btn-delete', function(e) {
                        e.preventDefault();

                        const formId = $(this).closest('form').attr('id');

                        swal({
                            title: 'Hapus Data',
                            text: 'Apakah Anda yakin ingin menghapus data ini secara permanen?',
                            icon: 'warning',
                            buttons: {
                                cancel: 'Batal',
                                confirm: {
                                    text: 'Ya, Hapus!',
                                    value: true,
                                    className: 'btn-danger',
                                }
                            },
                            dangerMode: true,
                        }).then((willDelete) => {
                            if (willDelete) {
                                $('#' + formId).submit();
                            }
                        });
                    });
                });
            </script>
        @endif
    @endif
@endpush

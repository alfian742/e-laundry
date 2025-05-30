@extends('layouts.dashboard')

@section('title', 'Pesanan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/datatables/dataTables.min.css') }}">
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
                                <form method="GET" action="{{ route('order.index') }}" style="width: 32rem">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">Saring</span>
                                        </div>

                                        <!-- Filter status pesanan -->
                                        <label class="d-none" for="order_status">&nbsp;</label>
                                        <select class="custom-select" name="order_status" id="order_status">
                                            <option value="all" {{ $orderStatus === 'all' ? 'selected' : '' }}>
                                                Semua Pesanan
                                            </option>
                                            <option value="new" {{ $orderStatus === 'new' ? 'selected' : '' }}>
                                                Baru
                                            </option>
                                            <option value="pending" {{ $orderStatus === 'pending' ? 'selected' : '' }}>
                                                Menunggu
                                            </option>
                                            <option value="pickup" {{ $orderStatus === 'pickup' ? 'selected' : '' }}>
                                                Dijemput
                                            </option>
                                            <option value="in_progress"
                                                {{ $orderStatus === 'in_progress' ? 'selected' : '' }}>
                                                Diproses
                                            </option>
                                            <option value="delivery" {{ $orderStatus === 'delivery' ? 'selected' : '' }}>
                                                Diantar
                                            </option>
                                            <option value="done" {{ $orderStatus === 'done' ? 'selected' : '' }}>
                                                Selesai
                                            </option>
                                            <option value="canceled" {{ $orderStatus === 'canceled' ? 'selected' : '' }}>
                                                Dibatalkan
                                            </option>
                                        </select>

                                        <!-- Filter status pembayaran -->
                                        <label class="d-none" for="payment_status">&nbsp;</label>
                                        <select class="custom-select" name="payment_status" id="payment_status">
                                            <option value="all" {{ $paymentStatus === 'all' ? 'selected' : '' }}>
                                                Semua Pembayaran
                                            </option>
                                            <option value="unpaid" {{ $paymentStatus === 'unpaid' ? 'selected' : '' }}>
                                                Belum Dibayar
                                            </option>
                                            <option value="partial" {{ $paymentStatus === 'partial' ? 'selected' : '' }}>
                                                Belum Lunas
                                            </option>
                                            <option value="paid" {{ $paymentStatus === 'paid' ? 'selected' : '' }}>
                                                Lunas
                                            </option>
                                        </select>

                                        <!-- Tombol Tampilkan -->
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">Tampilkan</button>
                                        </div>
                                    </div>
                                </form>

                                @if (!$isCustomer)
                                    <a href="{{ url('/order/create') }}" class="btn btn-primary ml-auto">Buat Pesanan</a>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-nowrap table-align-middle" id="table-1">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Dibuat Pada</th>
                                            <th>Kode Pesanan</th>
                                            @if (!$isCustomer)
                                                <th>Nama Pelanggan</th>
                                                <th>Tipe Pelanggan</th>
                                                <th>Nomor HP/WA</th>
                                            @endif
                                            <th>Metode Antar/Jemput</th>
                                            <th>Status Pesanan</th>
                                            <th>Total Pembayaran</th>
                                            <th>Status Pembayaran</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($orders as $order)
                                            @php
                                                $customer = $order?->orderingCustomer;
                                                $delivery = $order?->deliveryOption;
                                                $orderDetails = $order?->orderDetails;
                                                $hasUnweighedItem = collect($orderDetails)->contains(function ($item) {
                                                    return $item->weight_kg == 0; // loose comparison: aman di kasus ini
                                                });

                                                $orderCode = $order->order_code ?? 'N/A';

                                                $fullname =
                                                    $customer && $customer->fullname ? $customer->fullname : 'N/A';
                                                $customerType =
                                                    $customer && $customer->customer_type
                                                        ? ($customer->customer_type === 'member'
                                                            ? 'Member'
                                                            : 'Non-Member')
                                                        : 'N/A';
                                                $phoneNumber =
                                                    $customer && $customer->phone_number
                                                        ? $customer->phone_number
                                                        : 'N/A';

                                                $tooltip = $orderCode . " ({$fullname} - {$customerType})";
                                            @endphp

                                            <tr data-toggle="tooltip" title="{{ $tooltip }}" data-placement="right">
                                                <td>
                                                    {{ carbon_format_date($order->created_at, 'datetime') . " $zone" }}
                                                </td>
                                                <td>
                                                    <div class="clipboard d-flex justify-content-between align-items-center"
                                                        style="gap: .5rem">
                                                        <div class="clipboard-text">
                                                            {{ $orderCode }}
                                                        </div>
                                                        <button class="btn btn-clipboard" type="button"
                                                            data-toggle="tooltip" title="Salin kode pesanan"
                                                            onclick="copyToClipboard(event)">
                                                            <i class="fas fa-clipboard"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                @if (!$isCustomer)
                                                    <td>
                                                        @if ($customer)
                                                            <a href="{{ url("/customer/{$customer->id}") }}">
                                                                {{ $fullname }}
                                                            </a>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $customerType }}
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $phoneNumber }}
                                                    </td>
                                                @endif
                                                <td>
                                                    <div class="text-wrap-overflow">
                                                        {{ $delivery->method_name ?? 'N/A' }}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if (!$isCustomer)
                                                        <form method="POST"
                                                            action="{{ route('order.update.status', $order->id) }}"
                                                            style="width: 13rem">
                                                            @csrf
                                                            @method('put')
                                                            <div class="input-group">
                                                                <select class="custom-select" name="order_status"
                                                                    id="order_status">
                                                                    <option value="new"
                                                                        {{ $order->order_status === 'new' ? 'selected' : '' }}>
                                                                        Baru
                                                                    </option>
                                                                    <option value="pending"
                                                                        {{ $order->order_status === 'pending' ? 'selected' : '' }}>
                                                                        Menunggu
                                                                    </option>
                                                                    <option value="pickup"
                                                                        {{ $order->order_status === 'pickup' ? 'selected' : '' }}>
                                                                        Dijemput
                                                                    </option>
                                                                    <option value="in_progress"
                                                                        {{ $order->order_status === 'in_progress' ? 'selected' : '' }}>
                                                                        Diproses
                                                                    </option>
                                                                    <option value="delivery"
                                                                        {{ $order->order_status === 'delivery' ? 'selected' : '' }}>
                                                                        Diantar
                                                                    </option>
                                                                    <option value="done"
                                                                        {{ $order->order_status === 'done' ? 'selected' : '' }}>
                                                                        Selesai
                                                                    </option>
                                                                    <option value="canceled"
                                                                        {{ $order->order_status === 'canceled' ? 'selected' : '' }}>
                                                                        Dibatalkan
                                                                    </option>
                                                                </select>
                                                                <label class="d-none" for="order_status">&nbsp;</label>
                                                                <div class="input-group-append">
                                                                    <button type="submit"
                                                                        class="btn btn-primary">Simpan</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    @else
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
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    @if (!$hasUnweighedItem && count($orderDetails) > 0)
                                                        {{ formatRupiah($order->final_price) ?? '-' }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-center">
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
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center" style="gap: .5rem">
                                                        <a href="{{ url("/order/{$order->id}") }}" class="btn btn-info"
                                                            data-toggle="tooltip" title="Detail">
                                                            <i class="fas fa-info-circle"></i>
                                                        </a>

                                                        @if (
                                                            $isOwner ||
                                                                $isAdmin ||
                                                                ($isCustomer &&
                                                                    !in_array($order->order_status, ['new', 'done', 'canceled']) &&
                                                                    $order->payment_status !== 'paid'))
                                                            <a href="{{ url("/order/{$order->id}/transaction") }}"
                                                                class="btn btn-success" data-toggle="tooltip"
                                                                title="Kelola Pembayaran">
                                                                <i class="fas fa-credit-card"></i>
                                                            </a>
                                                        @endif

                                                        @if ($isOwner || $isAdmin || $isEmployee || ($isCustomer && $order->order_status === 'new'))
                                                            <a href="{{ url("/order/{$order->id}/edit") }}"
                                                                class="btn btn-primary" data-toggle="tooltip"
                                                                title="Ubah Pesanan">
                                                                <i class="fas fa-pencil-alt"></i>
                                                            </a>

                                                            @if ($isCustomer)
                                                                <form
                                                                    action="{{ route('order.update.status', $order->id) }}"
                                                                    method="POST" id="canceled-form-{{ $order->id }}"
                                                                    class="d-inline">
                                                                    @csrf
                                                                    @method('put')
                                                                    <input type="hidden" name="order_status"
                                                                        value="canceled">
                                                                    <button type="submit"
                                                                        class="btn btn-danger btn-canceled"
                                                                        data-toggle="tooltip" title="Batalkan Pesanan">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @elseif (!$isEmployee && ($isOwner || $isAdmin))
                                                                <form action="{{ route('order.destroy', $order->id) }}"
                                                                    method="POST" id="delete-form-{{ $order->id }}"
                                                                    class="d-inline">
                                                                    @csrf
                                                                    @method('delete')
                                                                    <button type="submit"
                                                                        class="btn btn-danger btn-delete"
                                                                        data-toggle="tooltip" title="Hapus">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('modules/datatables/dataTables.min.js') }}"></script>
    <script src="{{ asset('js/page/modules-datatables.js') }}"></script>

    @if ($isCustomer)
        <script>
            $(document).ready(function() {
                // Gunakan delegasi untuk tombol batalkan pesanan
                $(document).on('click', '.btn-canceled', function(e) {
                    e.preventDefault();

                    const formId = $(this).closest('form').attr('id');

                    swal({
                        title: 'Batalkan Pesanan',
                        text: 'Apakah Anda yakin ingin membatalkan pesanan ini?',
                        icon: 'warning',
                        buttons: {
                            cancel: 'Batal',
                            confirm: {
                                text: 'Ya, Batalkan!',
                                value: true,
                                className: 'btn-danger',
                            }
                        },
                        dangerMode: true,
                    }).then((willCanceled) => {
                        if (willCanceled) {
                            $('#' + formId).submit();
                        }
                    });
                });
            });
        </script>
    @elseif (!$isEmployee && ($isOwner || $isAdmin))
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
@endpush

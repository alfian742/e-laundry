@extends('layouts.dashboard')

@section('title', 'Ubah Pesanan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/select2/dist/css/select2-bootstrap4.min.css') }}">
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

        @media (min-width: 768px) {
            .w-md-100 {
                width: 100%;
            }
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
                            <div class="row g-4 justify-content-center">
                                @if (!$isCustomer)
                                    <div class="col-12">
                                        <form method="POST" action="{{ route('order.service.store', $order->id) }}"
                                            class="row g-4 mb-4">
                                            <div class="col-sm-12 col-md-6">
                                                @csrf
                                            </div>
                                            <div class="col-sm-12 col-md-4 mb-4 mb-md-0 pr-md-1">
                                                <div class="form-group mb-0">
                                                    <label class="d-none" for="service_id">&nbsp;</label>
                                                    <select name="service_id" id="service_id"
                                                        class="custom-select select2 custom-select2 @error('service_id') is-invalid @enderror">
                                                        <option value="" selected disabled>
                                                            @if ($services->isEmpty())
                                                                -- Tidak ada layanan yang tersedia --
                                                            @else
                                                                -- Pilih Layanan --
                                                            @endif
                                                        </option>
                                                        @foreach ($services as $service)
                                                            <option value="{{ $service->id }}"
                                                                {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                                                {{ $service->service_name ?? 'N/A' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        @error('service_id')
                                                            {{ $message }}
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-2 pl-md-2 text-right text-md-left">
                                                <button type="submit" class="btn btn-primary w-md-100 py-2"
                                                    {{ $services->isEmpty() ? 'disabled' : '' }}>
                                                    Tambahkan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                                @if (!$details->isEmpty())
                                    <div class="col-12">
                                        @if (!$isCustomer)
                                            <div class="alert bg-light text-dark mb-4" role="alert">
                                                <h6 class="mb-2">Penerapan Promo</h6>
                                                <p class="text-lead mb-0">
                                                    Sistem akan secara otomatis memilih promo yang tersedia pada setiap
                                                    layanan berdasarkan tipe pelanggan (Member/Non-Member) serta tipe promo
                                                    yang berlaku (Harian atau Periode tertentu) dengan diskon yang paling
                                                    besar.
                                                    Anda juga dapat mengatur promo secara manual jika diperlukan.
                                                </p>
                                            </div>
                                        @endif

                                        <form action="{{ route('order.update', $order->id) }}" method="POST">
                                            @csrf
                                            @method('put')

                                            <div class="table-responsive mb-4">
                                                <table
                                                    class="table table-sm table-bordered table-nowrap table-align-middle">
                                                    <thead class="bg-light text-center">
                                                        <tr>
                                                            <th rowspan="2">Nama Layanan</th>
                                                            <th rowspan="2">Harga per (Kg/Item)</th>
                                                            <th rowspan="2">
                                                                {!! $isCustomer ? '<span class="text-danger">*</span>' : '' !!}
                                                                Berat (Kg)/Jumlah (Item)
                                                            </th>
                                                            <th rowspan="2">Subtotal</th>
                                                            <th rowspan="2">Promo</th>
                                                            <th colspan="2">
                                                                {!! $isCustomer ? '<span class="text-danger">*</span>' : '' !!} Diskon
                                                            </th>
                                                            <th rowspan="2">
                                                                Total
                                                                <br>
                                                                (Subtotal - Diskon)
                                                            </th>
                                                            @if (!$isCustomer)
                                                                <th rowspan="2">Aksi</th>
                                                            @endif
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
                                                                    @if (!$isCustomer)
                                                                        <a
                                                                            href="{{ url("/service/{$service->id}") }}">{{ $service->service_name ?? '-' }}</a>
                                                                    @else
                                                                        {{ $service->service_name ?? '-' }}
                                                                    @endif
                                                                </td>
                                                                <td class="text-right">
                                                                    <span id="price_per_kg-{{ $detail->id }}"
                                                                        data-price_per_kg="{{ $detail->price_per_kg }}">{{ formatRupiah($detail->price_per_kg) ?? '-' }}</span>
                                                                </td>
                                                                @if (!$isCustomer)
                                                                    <td>
                                                                        <div class="form-group mb-0">
                                                                            <label for="weight_kg-{{ $detail->id }}"
                                                                                class="d-none">&nbsp;</label>
                                                                            <div class="input-group">
                                                                                <div class="input-group-prepend">
                                                                                    <button
                                                                                        class="btn btn-primary btn-minus"
                                                                                        type="button"
                                                                                        data-id="{{ $detail->id }}">
                                                                                        <i class="fas fa-minus"></i>
                                                                                    </button>
                                                                                </div>
                                                                                <input id="weight_kg-{{ $detail->id }}"
                                                                                    type="text"
                                                                                    class="form-control text-center @error('weight_kg.' . $detail->id) is-invalid @enderror"
                                                                                    name="weight_kg[{{ $detail->id }}]"
                                                                                    value="{{ old('weight_kg.' . $detail->id, intval($detail->weight_kg)) }}"
                                                                                    placeholder="0">
                                                                                <div class="input-group-append">
                                                                                    <button class="btn btn-primary btn-plus"
                                                                                        style="border-start-end-radius: .25rem; border-end-end-radius: .25rem;"
                                                                                        type="button"
                                                                                        data-id="{{ $detail->id }}">
                                                                                        <i class="fas fa-plus"></i>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="invalid-feedback text-center">
                                                                                    @error('weight_kg.' . $detail->id)
                                                                                        {{ $message }}
                                                                                    @enderror
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                @else
                                                                    <td class="text-right">
                                                                        {{ intval($detail->weight_kg) }}
                                                                    </td>
                                                                @endif
                                                                <td class="text-right" style="min-width: 10rem;">
                                                                    <span id="total_price-{{ $detail->id }}"
                                                                        data-total_price="{{ $detail->total_price }}">{{ formatRupiah($detail->total_price) ?? '-' }}</span>
                                                                </td>
                                                                @if (!$isCustomer)
                                                                    <td>
                                                                        @php
                                                                            $today = \Carbon\Carbon::now();
                                                                            $todayName = strtolower(
                                                                                $today->format('l'),
                                                                            );

                                                                            $filteredPromos = $service->promos
                                                                                ->where('active', true)
                                                                                ->filter(function ($promo) use (
                                                                                    $today,
                                                                                    $todayName,
                                                                                ) {
                                                                                    if (
                                                                                        $promo->promo_type === 'daily'
                                                                                    ) {
                                                                                        return $promo->day_of_week ===
                                                                                            $todayName;
                                                                                    } elseif (
                                                                                        $promo->promo_type ===
                                                                                        'date_range'
                                                                                    ) {
                                                                                        return $today->between(
                                                                                            \Carbon\Carbon::parse(
                                                                                                $promo->start_date,
                                                                                            )->startOfDay(),
                                                                                            \Carbon\Carbon::parse(
                                                                                                $promo->end_date,
                                                                                            )->endOfDay(),
                                                                                        );
                                                                                    }
                                                                                    return false;
                                                                                });

                                                                            // Simpan promo diskon terbesar, nanti akan dipilih otomatis lewat JS jika cocok dengan customer_type
                                                                            $autoSelectedPromo = $filteredPromos
                                                                                ->sortByDesc('discount_percent')
                                                                                ->first();
                                                                        @endphp

                                                                        <div class="form-group mb-0"
                                                                            style="width: 17.5rem;">
                                                                            @if ($filteredPromos->isNotEmpty())
                                                                                <label class="d-none"
                                                                                    for="promo_id-{{ $detail->id }}">&nbsp;</label>
                                                                                <select
                                                                                    name="promo_id[{{ $detail->id }}]"
                                                                                    id="promo_id-{{ $detail->id }}"
                                                                                    class="custom-select select2 promo-select"
                                                                                    data-detail_id="{{ $detail->id }}"
                                                                                    data-best_promo_id="{{ $autoSelectedPromo?->id ?? '' }}">
                                                                                    <option value=""
                                                                                        data-discount_percent="0"
                                                                                        data-customer_scope="">-- Pilih
                                                                                        Promo --</option>
                                                                                    @foreach ($filteredPromos as $promo)
                                                                                        <option value="{{ $promo->id }}"
                                                                                            data-discount_percent="{{ intval($promo->discount_percent) }}"
                                                                                            data-customer_scope="{{ $promo->customer_scope }}">
                                                                                            {{ $promo->promo_name ?? 'N/A' }}
                                                                                            ({{ $promo->customer_scope === 'member' ? 'Member' : 'Non-Member' }})
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                                <div class="invalid-feedback">
                                                                                    Pilih pelanggan terlebih dahulu
                                                                                </div>
                                                                            @else
                                                                                <input type="hidden"
                                                                                    name="promo_id[{{ $detail->id }}]"
                                                                                    value="">
                                                                                <div class="text-muted">Tidak ada promo
                                                                                    yang tersedia</div>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                @else
                                                                    <td>
                                                                        {{ $order?->includedPromo?->promo_name ?? '-' }}
                                                                    </td>
                                                                @endif
                                                                <td class="text-right" style="min-width: 5rem;">
                                                                    <span id="discount_percent-{{ $detail->id }}">
                                                                        {{ $detail->discount_percent ? intval($detail->discount_percent) . '%' : '-' }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-right" style="min-width: 10rem;">
                                                                    <span id="discount_price-{{ $detail->id }}">
                                                                        {{ formatRupiah($detail->total_price * ($detail->discount_percent / 100)) ?? '-' }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-right" style="min-width: 10rem;">
                                                                    <span id="final_service_price-{{ $detail->id }}">
                                                                        {{ formatRupiah($detail->final_service_price) ?? '-' }}
                                                                    </span>
                                                                </td>
                                                                @if (!$isCustomer)
                                                                    <td class="text-center" style="min-width: 5rem;">
                                                                        <button type="button"
                                                                            class="btn btn-danger btn-delete"
                                                                            data-action="{{ route('order.service.destroy', ['order' => $order->id, 'detail' => $detail->id]) }}"
                                                                            data-toggle="tooltip" title="Hapus">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                            @php $finalServicePrice += $detail->final_service_price; @endphp
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="bg-light">
                                                        <tr>
                                                            <td colspan="7" class="text-center font-weight-bold">
                                                                Total
                                                                Harga Pesanan</td>
                                                            <td class="text-right font-weight-bold">
                                                                <span id="total">
                                                                    {{ formatRupiah($finalServicePrice) ?? '-' }}
                                                                </span>
                                                            </td>
                                                            @if (!$isCustomer)
                                                                <td>&nbsp;</td>
                                                            @endif
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>

                                            @if ($isCustomer)
                                                <div class="mb-4">
                                                    <div class="section-title">Keterangan</div>
                                                    <p class="section-lead mb-1">
                                                        <span class="bullet"></span> <strong><span
                                                                class="text-danger">*</span>
                                                            Berat (Kg)/Jumlah (Item)</strong>
                                                        akan diisi oleh Admin berdasarkan hasil penimbangan atau jumlah item
                                                        yang diterima.
                                                    </p>
                                                    <p class="section-lead mb-1">
                                                        <span class="bullet"></span> <strong><span
                                                                class="text-danger">*</span>
                                                            Diskon</strong> akan diberikan
                                                        sesuai dengan syarat dan ketentuan promo yang berlaku.
                                                    </p>
                                                    <p class="section-lead mb-0">
                                                        <span class="bullet"></span> Layanan pada pesanan ini <strong>tidak
                                                            dapat diubah</strong>
                                                    </p>
                                                </div>
                                            @endif

                                            <hr class="my-4">

                                            <div class="row g-4">
                                                @if (!$isCustomer)
                                                    <div class="form-group col-md-6">
                                                        <label for="customer_id">Nama Pelanggan <span
                                                                class="text-danger">*</span></label>
                                                        <select name="customer_id" id="customer_id"
                                                            class="custom-select select2 @error('customer_id') is-invalid @enderror">
                                                            <option value="" disabled selected>-- Pilih Pelanggan --
                                                            </option>
                                                            @foreach ($customers as $customer)
                                                                <option value="{{ $customer->id }}"
                                                                    data-customer_type ="{{ $customer->customer_type }}"
                                                                    {{ old('customer_id', $order->customer_id) == $customer->id ? 'selected' : '' }}>
                                                                    {{ $customer->fullname ?? 'N/A' }}
                                                                    ({{ $customer->customer_type === 'member' ? 'Member' : 'Non-Member' }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            @error('customer_id')
                                                                {{ $message }}
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="form-group col-md-6">
                                                        <label for="order_code">Kode Pesanan <span
                                                                class="text-danger">*</span></label>
                                                        <input id="order_code" type="text"
                                                            class="form-control bg-light @error('order_code') is-invalid @enderror"
                                                            name="order_code"
                                                            value="{{ old('order_code', $order->order_code) }}"
                                                            placeholder="Dibuat otomatis oleh sistem" readonly>
                                                        <div class="invalid-feedback">
                                                            @error('order_code')
                                                                {{ $message }}
                                                            @enderror
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="form-group col-md-6">
                                                    <label for="delivery_method_id">Metode Antar/Jemput <span
                                                            class="text-danger">*</span></label>
                                                    <select name="delivery_method_id" id="delivery_method_id"
                                                        class="custom-select select2 @error('delivery_method_id') is-invalid @enderror">
                                                        <option value="" disabled selected>-- Pilih --
                                                        </option>
                                                        @foreach ($deliveryMethods as $method)
                                                            <option value="{{ $method->id }}"
                                                                data-delivery-cost={{ formatRupiahPlain($method->cost) }}
                                                                {{ old('delivery_method_id', $order->delivery_method_id) == $method->id ? 'selected' : '' }}>
                                                                {{ $method->method_name ?? 'N/A' }}
                                                                {{ $method->description ? '(' . $method->description . ')' : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="invalid-feedback">
                                                        @error('delivery_method_id')
                                                            {{ $message }}
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group col-md-6">
                                                    <label for="delivery_cost">Biaya Antar/Jemput <span
                                                            class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text bg-light">Rp</span>
                                                        </div>
                                                        <input id="delivery_cost" type="text"
                                                            class="form-control bg-light @error('delivery_cost') is-invalid @enderror"
                                                            style="border-start-end-radius: .25rem; border-end-end-radius: .25rem;"
                                                            name="delivery_cost"
                                                            value="{{ formatRupiahPlain(old('delivery_cost', $order->delivery_cost)) }}"
                                                            placeholder="0" readonly>
                                                        <div class="invalid-feedback">
                                                            @error('delivery_cost')
                                                                {{ $message }}
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group col-md-6">
                                                    <label for="pickup_date">Tanggal Jemput (Opsional)</label>
                                                    <input id="pickup_date" type="date"
                                                        class="form-control @error('pickup_date') is-invalid @enderror"
                                                        name="pickup_date"
                                                        value="{{ old('pickup_date', $order->pickup_date) }}">
                                                    <div class="invalid-feedback">
                                                        @error('pickup_date')
                                                            {{ $message }}
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group col-md-6">
                                                    <label for="pickup_time">Waktu Jemput (Opsional)</label>
                                                    <input id="pickup_time" type="time"
                                                        class="form-control @error('pickup_time') is-invalid @enderror"
                                                        name="pickup_time"
                                                        value="{{ carbon_format_date(old('pickup_time', $order->pickup_time), 'time') }}">
                                                    <div class="invalid-feedback">
                                                        @error('pickup_time')
                                                            {{ $message }}
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group col-md-6">
                                                    <label for="delivery_date">Tanggal Antar (Opsional)</label>
                                                    <input id="delivery_date" type="date"
                                                        class="form-control @error('delivery_date') is-invalid @enderror"
                                                        name="delivery_date"
                                                        value="{{ old('delivery_date', $order->delivery_date) }}">
                                                    <div class="invalid-feedback">
                                                        @error('delivery_date')
                                                            {{ $message }}
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group col-md-6">
                                                    <label for="delivery_time">Waktu Antar (Opsional)</label>
                                                    <input id="delivery_time" type="time"
                                                        class="form-control @error('delivery_time') is-invalid @enderror"
                                                        name="delivery_time"
                                                        value="{{ carbon_format_date(old('delivery_time', $order->delivery_time), 'time') }}">
                                                    <div class="invalid-feedback">
                                                        @error('delivery_time')
                                                            {{ $message }}
                                                        @enderror
                                                    </div>
                                                </div>

                                                @if (!$isCustomer)
                                                    <div class="form-group col-md-6">
                                                        <label for="order_status">Status Pesanan <span
                                                                class="text-danger">*</span></label>
                                                        <select name="order_status" id="order_status"
                                                            class="custom-select @error('order_status') is-invalid @enderror">
                                                            <option value="" disabled
                                                                {{ old('order_status', $order->order_status) === null ? 'selected' : '' }}>
                                                                -- Pilih Status Pesanan --
                                                            </option>
                                                            <option value="new"
                                                                {{ old('order_status', $order->order_status) === 'new' ? 'selected' : '' }}>
                                                                Baru
                                                            </option>
                                                            <option value="pending"
                                                                {{ old('order_status', $order->order_status) === 'pending' ? 'selected' : '' }}>
                                                                Menunggu
                                                            </option>
                                                            <option value="pickup"
                                                                {{ old('order_status', $order->order_status) === 'pickup' ? 'selected' : '' }}>
                                                                Dijemput
                                                            </option>
                                                            <option value="in_progress"
                                                                {{ old('order_status', $order->order_status) === 'in_progress' ? 'selected' : '' }}>
                                                                Diproses
                                                            </option>
                                                            <option value="delivery"
                                                                {{ old('order_status', $order->order_status) === 'delivery' ? 'selected' : '' }}>
                                                                Diantar
                                                            </option>
                                                            <option value="done"
                                                                {{ old('order_status', $order->order_status) === 'done' ? 'selected' : '' }}>
                                                                Selesai
                                                            </option>
                                                            <option value="canceled"
                                                                {{ old('order_status', $order->order_status) === 'canceled' ? 'selected' : '' }}>
                                                                Dibatalkan
                                                            </option>
                                                        </select>
                                                        <div class="invalid-feedback">
                                                            @error('order_status')
                                                                {{ $message }}
                                                            @enderror
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="form-group col-md-6">
                                                    <label for="notes">Catatan (Opsional)</label>
                                                    <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                                        placeholder="Tulis catatan...">{{ old('notes', $order->notes) }}</textarea>
                                                    <div class="invalid-feedback">
                                                        @error('notes')
                                                            {{ $message }}
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <hr class="my-4">

                                                    <div class="table-responsive mb-4 mb-md-0">
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
                                                                    <span id="total_copy">
                                                                        {{ formatRupiah($finalServicePrice) ?? '-' }}
                                                                    </span>
                                                                    +
                                                                    <span id="delivery_cost_copy">
                                                                        {{ formatRupiah($order->delivery_cost) ?? '-' }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th>Total Pembayaran</th>
                                                                <td>=</td>
                                                                <td>
                                                                    <span id="final_amount_paid">
                                                                        @php $finalAmountPaid = $finalServicePrice + $order->delivery_cost @endphp

                                                                        {{ formatRupiah($finalAmountPaid) ?? '-' }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="d-flex justify-content-center justify-content-sm-end align-items-center"
                                                        style="gap: .5rem">
                                                        <a href="{{ url('/order') }}"
                                                            class="btn btn-secondary">Kembali</a>

                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('modules/select2/dist/js/select2.full.min.js') }}"></script>

    <script>
        // Fungsi utilitas global
        function formatToRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        }

        function parseRupiahToNumber(rupiahText) {
            if (!rupiahText) return 0;
            return parseInt(rupiahText.replace(/[^\d]/g, '')) || 0;
        }

        $(document).ready(function() {
            $('#delivery_method_id').on('change', function() {
                const cost = $(this).find('option:selected').data('delivery-cost');
                $('#delivery_cost').val(cost || 0).trigger('input');
            });

            if ($('#delivery_method_id').val()) {
                $('#delivery_method_id').trigger('change');
            }
        });
    </script>

    @if (!$isCustomer)
        <script>
            $(document).ready(function() {
                function updateFinalPrice(id) {
                    const pricePerKg = parseFloat($(`#price_per_kg-${id}`).data('price_per_kg')) || 0;
                    const weight = parseInt($(`#weight_kg-${id}`).val()) || 0;
                    const selectedOption = $(`#promo_id-${id}`).find(':selected');
                    const discountPercent = parseInt(selectedOption.data('discount_percent')) || 0;
                    const totalPrice = pricePerKg * weight;
                    const discountPrice = totalPrice * (discountPercent / 100);
                    const finalPrice = totalPrice - discountPrice;

                    $(`#total_price-${id}`).text(formatToRupiah(totalPrice)).data('total_price', totalPrice);
                    $(`#discount_percent-${id}`).text(discountPercent ? `${discountPercent}%` : '0%');
                    $(`#discount_price-${id}`).text(formatToRupiah(discountPrice));
                    $(`#final_service_price-${id}`).text(formatToRupiah(finalPrice)).data('final_service_price',
                        finalPrice);

                    updateGrandTotal();
                }

                function updateGrandTotal() {
                    let grandTotal = 0;
                    $('[id^="final_service_price-"]').each(function() {
                        grandTotal += parseFloat($(this).data('final_service_price')) || 0;
                    });
                    $('#total').text(formatToRupiah(grandTotal));
                }

                // === FILTER & PILIH OTOMATIS PROMO BERDASARKAN customer_type ===
                function filterPromosByCustomerType(customerType) {
                    $('[id^="promo_id-"]').each(function() {
                        const select = $(this);
                        const detailId = select.attr('id').split('-')[1];

                        let bestPromo = null;
                        let maxDiscount = 0;

                        select.find('option').each(function() {
                            const option = $(this);
                            const scope = option.data('customer_scope');
                            const discount = parseInt(option.data('discount_percent')) || 0;

                            if (!scope || scope === customerType) {
                                option.show();
                                if (discount > maxDiscount) {
                                    bestPromo = option;
                                    maxDiscount = discount;
                                }
                            } else {
                                option.hide();
                            }
                        });

                        if (bestPromo && bestPromo.val()) {
                            select.val(bestPromo.val()).trigger('change');
                        } else {
                            // Tidak ada yang cocok, kosongkan
                            select.val('').trigger('change');
                        }

                        updateFinalPrice(detailId);
                    });
                }

                $('#customer_id').on('change', function() {
                    const customerType = $('#customer_id option:selected').data('customer_type');
                    const customerValue = $(this).val();

                    if (!customerValue) {
                        $('.promo-select')
                            .addClass('is-invalid')
                            .prop('disabled', true); // Nonaktifkan select jika value tidak ada
                    } else {
                        $('.promo-select')
                            .removeClass('is-invalid')
                            .prop('disabled', false); // Aktifkan kembali jika value valid
                        filterPromosByCustomerType(customerType);
                    }
                });

                // Saat halaman pertama kali dimuat
                const initialType = $('#customer_id option:selected').data('customer_type');
                const initialCustomerValue = $('#customer_id').val();
                if (!initialCustomerValue) {
                    $('.promo-select')
                        .addClass('is-invalid')
                        .prop('disabled', true); // Menonaktifkan select
                } else {
                    $('.promo-select')
                        .removeClass('is-invalid')
                        .prop('disabled', false); // Mengaktifkan kembali select jika valid
                    filterPromosByCustomerType(initialType);
                }

                $('input[id^="weight_kg-"]').on('input', function() {
                    const id = $(this).attr('id').split('-')[1];
                    updateFinalPrice(id);
                });

                $('.btn-plus').on('click', function() {
                    const id = $(this).data('id');
                    const input = $(`#weight_kg-${id}`);
                    const value = parseInt(input.val()) || 0;
                    input.val(value + 1).trigger('input');
                });

                $('.btn-minus').on('click', function() {
                    const id = $(this).data('id');
                    const input = $(`#weight_kg-${id}`);
                    const value = parseInt(input.val()) || 0;
                    input.val(value > 0 ? value - 1 : 0).trigger('input');
                });

                $(document).on('change', 'select[id^="promo_id-"]', function() {
                    const id = $(this).attr('id').split('-')[1];
                    updateFinalPrice(id);
                });

                // Hitung awal semua
                $('input[id^="weight_kg-"]').each(function() {
                    const id = $(this).attr('id').split('-')[1];
                    updateFinalPrice(id);
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                $(document).on('click', '.btn-delete', function(e) {
                    e.preventDefault();

                    const actionUrl = $(this).data('action');

                    swal({
                        title: 'Hapus Data',
                        text: 'Apakah Anda yakin ingin menghapus layanan untuk pesanan ini?',
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
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = actionUrl;
                            form.style.display = 'none';

                            // CSRF token dari Laravel (inline dari Blade)
                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = '{{ csrf_token() }}';
                            form.appendChild(csrfInput);

                            // Method spoofing untuk DELETE
                            const methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            methodInput.value = 'delete';
                            form.appendChild(methodInput);

                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });
        </script>
    @endif

    <script>
        $(document).ready(function() {
            function updateTotalPayment() {
                const totalText = $("#total").text();
                const deliveryInput = $("#delivery_cost").val();

                const total = parseRupiahToNumber(totalText);
                const deliveryCost = parseRupiahToNumber(deliveryInput);
                const finalTotal = total + deliveryCost;

                $("#total_copy").text(formatToRupiah(total));
                $("#delivery_cost_copy").text(formatToRupiah(deliveryCost));
                $("#final_amount_paid").text(formatToRupiah(finalTotal));
            }

            updateTotalPayment();

            $("#delivery_cost").on("input", function() {
                updateTotalPayment();
            });

            const totalNode = document.getElementById('total');
            if (totalNode) {
                const observer = new MutationObserver(function(mutationsList) {
                    for (const mutation of mutationsList) {
                        if (mutation.type === 'childList' || mutation.type === 'characterData') {
                            updateTotalPayment();
                        }
                    }
                });

                observer.observe(totalNode, {
                    childList: true,
                    characterData: true,
                    subtree: true
                });
            }
        });
    </script>
@endpush

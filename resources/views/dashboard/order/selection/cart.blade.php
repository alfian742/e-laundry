@extends('layouts.dashboard')

@section('title', 'Keranjang Pesanan')

@push('styles')
    @if (!$carts->isEmpty())
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
        </style>
    @endif
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
                                <a href="{{ url('/order') }}" class="btn btn-outline-primary"><i
                                        class="fas fa-dolly mr-1"></i> Semua Pesanan</a>
                                <a href="{{ url('/order/services') }}" class="btn btn-primary"><i
                                        class="fas fa-jug-detergent mr-1"></i> Tambah Layanan</a>
                            </div>

                            <div class="row g-4 justify-content-center">
                                @if (!$carts->isEmpty())
                                    <div class="col-12">
                                        <div class="table-responsive mb-4">
                                            <table class="table table-sm table-bordered table-nowrap table-align-middle">
                                                <thead class="bg-light text-center">
                                                    <tr>
                                                        <th rowspan="2">Nama Layanan</th>
                                                        <th rowspan="2">Harga per (Kg/Item)</th>
                                                        <th rowspan="2">
                                                            <span class="text-danger">*</span>
                                                            Berat (Kg)/Jumlah (Item)
                                                        </th>
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
                                                        <th rowspan="2">Aksi</th>
                                                    </tr>
                                                    <tr>
                                                        <th>(%)</th>
                                                        <th>(Rp)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $finalServicePrice = 0 @endphp
                                                    @foreach ($carts as $cart)
                                                        @php
                                                            $service = $cart?->includedService;
                                                            $promo = $cart?->includedPromo;
                                                        @endphp

                                                        <tr>
                                                            <td>
                                                                {{ $service->service_name ?? '-' }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ formatRupiah($service->price_per_kg) ?? '-' }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ $cart->weight_kg ? intval($cart->weight_kg) : '-' }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ formatRupiah($cart->total_price) ?? '-' }}
                                                            </td>
                                                            <td>
                                                                {{ $promo->promo_name ?? '-' }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ $cart->discount_percent ? intval($cart->discount_percent) . '%' : '-' }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ formatRupiah($cart->total_price * ($cart->discount_percent / 100)) ?? '-' }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ formatRupiah($cart->final_service_price) ?? '-' }}
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="d-flex justify-content-center align-items-center"
                                                                    style="gap: .5rem">
                                                                    <form
                                                                        action="{{ route('order.cart.destroy', $cart->id) }}"
                                                                        method="POST" id="delete-form-{{ $cart->id }}"
                                                                        class="d-inline">
                                                                        @csrf
                                                                        @method('delete')
                                                                        <button type="submit"
                                                                            class="btn btn-danger btn-delete"
                                                                            data-toggle="tooltip" title="Hapus">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @php $finalServicePrice += $cart->final_service_price; @endphp
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="bg-light">
                                                    <tr>
                                                        <td colspan="7" class="text-center font-weight-bold">Total
                                                            Harga Pesanan</td>
                                                        <td class="text-right font-weight-bold">
                                                            {{ formatRupiah($finalServicePrice) ?? '-' }}
                                                        </td>
                                                        <td>&nbsp;</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <div class="mb-4">
                                            <div class="section-title">Keterangan</div>
                                            <p class="section-lead mb-1">
                                                <span class="bullet"></span> <strong><span class="text-danger">*</span>
                                                    Berat (Kg)/Jumlah (Item)</strong>
                                                akan diisi oleh Admin berdasarkan hasil penimbangan atau jumlah item
                                                yang diterima.
                                            </p>
                                            <p class="section-lead mb-1">
                                                <span class="bullet"></span> <strong><span class="text-danger">*</span>
                                                    Diskon</strong> akan diberikan
                                                sesuai dengan syarat dan ketentuan promo yang berlaku.
                                            </p>
                                            <p class="section-lead mb-0">
                                                <span class="bullet"></span> Setelah pesanan dibuat, Anda tidak dapat
                                                <strong>mengubah</strong>, <strong>menambah</strong>, maupun
                                                <strong>menghapus</strong> layanan.
                                            </p>
                                        </div>

                                        <hr class="my-4">

                                        <form action="{{ route('order.checkout', $order->id) }}" method="POST">
                                            @csrf
                                            @method('put')
                                            <div class="row g-4">
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
                                                                {{ old('delivery_method_id') == $method->id ? 'selected' : '' }}>
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
                                                            class="form-control @error('delivery_cost') is-invalid @enderror"
                                                            style="border-start-end-radius: .25rem; border-end-end-radius: .25rem;"
                                                            name="delivery_cost" value="{{ old('delivery_cost') }}"
                                                            placeholder="0" readonly>
                                                        <div class="invalid-feedback" id="price-per-kg-error">
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
                                                        name="pickup_date" value="{{ old('pickup_date') }}">
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
                                                        value="{{ carbon_format_date(old('pickup_time'), 'time') }}">
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
                                                        name="delivery_date" value="{{ old('delivery_date') }}">
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
                                                        value="{{ carbon_format_date(old('delivery_time'), 'time') }}">
                                                    <div class="invalid-feedback">
                                                        @error('delivery_time')
                                                            {{ $message }}
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group col-md-6">
                                                    <label for="notes">Catatan (Opsional)</label>
                                                    <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                                        placeholder="Tulis catatan...">{{ old('notes') }}</textarea>
                                                    <div class="invalid-feedback">
                                                        @error('notes')
                                                            {{ $message }}
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="d-flex justify-content-center justify-content-md-end align-items-center"
                                                        style="gap: .5rem">
                                                        <button type="submit" class="btn btn-primary">Buat
                                                            Pesanan</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    <div class="col-12 py-5">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="text-center">
                                                <h1><i class="fa-solid fa-shopping-cart"></i></h1>

                                                <p class="text-lead">Tidak ada layanan pada keranjang pesanan.</p>
                                            </div>
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
@endsection

@push('scripts')
    @if (!$carts->isEmpty())
        <script src="{{ asset('modules/select2/dist/js/select2.full.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                $('#delivery_method_id').on('change', function() {
                    var cost = $(this).find('option:selected').data('delivery-cost');
                    $('#delivery_cost').val(cost || 0);
                });

                if ($('#delivery_method_id').val()) {
                    $('#delivery_method_id').trigger('change');
                }
            });
        </script>
        <script>
            $(document).ready(function() {
                // Gunakan delegasi untuk tombol hapus
                $(document).on('click', '.btn-delete', function(e) {
                    e.preventDefault();

                    const formId = $(this).closest('form').attr('id');

                    swal({
                        title: 'Hapus Data',
                        text: 'Apakah Anda yakin ingin menghapus layanan dari keranjang?',
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

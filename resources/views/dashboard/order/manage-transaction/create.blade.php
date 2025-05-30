@extends('layouts.dashboard')

@section('title', 'Tambah Pembayaran')

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
            <div class="row justify-content-center align-items-center">
                @if ($isCustomer)
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <form action="{{ route('transaction.order.store', $order->id) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <label for="payment_method_id">Metode Pembayaran <span
                                                class="text-danger">*</span></label>
                                        <select name="payment_method_id" id="payment_method_id"
                                            class="custom-select select2 @error('payment_method_id') is-invalid @enderror">
                                            <option value="" disabled selected>-- Pilih Metode Pembayaran --
                                            </option>
                                            @foreach ($paymentMethods as $method)
                                                <option value="{{ $method->id }}"
                                                    data-payment-type="{{ $method->payment_type }}"
                                                    data-payment-description="{{ !empty($method->description) ? $method->description : 'empty_payment_description' }}"
                                                    data-payment-img-url="{{ !empty($method->img) ? get_image_url($method->img) : 'empty_payment_img' }}"
                                                    data-payment-img-name="{{ $method->method_name ?? 'N/A' }}"
                                                    {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>
                                                    {{ $method->method_name ?? 'N/A' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('payment_method_id')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- class -> visible-field-{payment_type} --}}
                                    <div
                                        class="p-0 d-none visible-field-manual visible-field-online visible-field-bank_transfer">
                                        <div class="alert border text-dark" role="alert">
                                            <div id="paymentDescriptionPreview" class="d-none">
                                                <h6>Keterangan</h6>
                                                <p id="paymentDescription" class="text-lead mb-0"></p>
                                            </div>

                                            <div id="paymenentImgPreview" class="d-none">
                                                <div class="mt-4">
                                                    <!-- style sebagai parameter -->
                                                    <div class="ratio ratio-1x1" style="max-width: 360px; margin: auto;">
                                                        <img id="paymenentImg" src="" alt="" loading="lazy"
                                                            class="rounded d-block w-100 h-100" style="object-fit: cover;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-none visible-field-online visible-field-bank_transfer">
                                        <div id="img-preview">
                                            <h6 class="text-small text-center">Pratinjau bukti pembayaran:</h6>
                                            <!-- style sebagai parameter -->
                                            <div class="ratio ratio-3x4" style="max-width: 360px; margin: auto;">
                                                <img src="{{ get_image_url('example-image.jpg') }}" alt="New Image"
                                                    loading="lazy" class="rounded d-block w-100 h-100"
                                                    style="object-fit: cover;">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="img" class="mt-3">Bukti Pembayaran <span
                                                    class="text-danger">*</span></label>
                                            <div class="custom-file">
                                                <input type="file"
                                                    class="custom-file-input @error('img') is-invalid @enderror"
                                                    id="img" name="img"
                                                    accept="image/png, image/jpeg, image/jpg, image/webp">
                                                <label class="custom-file-label" for="img"><span id="filename">Pilih
                                                        berkas...</span></label>
                                                <div class="invalid-feedback" id="img-preview-error">
                                                    @error('img')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        $finalServicePrice = 0;
                                        $totalAmountPaid = 0;

                                        foreach ($details as $detail) {
                                            $finalServicePrice += $detail->final_service_price;
                                        }

                                        foreach ($transactions as $transaction) {
                                            $totalAmountPaid += $transaction->amount_paid;
                                        }
                                    @endphp

                                    <hr class="my-4">

                                    <div class="table-responsive mb-4">
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
                                                </td>
                                            </tr>

                                            @if ($totalAmountPaid > 0)
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
                                            @endif
                                        </table>
                                    </div>

                                    <div class="d-flex justify-content-center align-items-center" style="gap: .5rem">
                                        <a href="{{ url("/order/{$order->id}/transaction") }}"
                                            class="btn btn-secondary">Kembali</a>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form action="{{ route('transaction.order.store', $order->id) }}" method="POST">
                                    @csrf
                                    <div class="row justify-content-center g-4">
                                        <div class="form-group col-md-6">
                                            <label for="payment_method_id">Metode Pembayaran <span
                                                    class="text-danger">*</span></label>
                                            <select name="payment_method_id" id="payment_method_id"
                                                class="custom-select select2 @error('payment_method_id') is-invalid @enderror">
                                                <option value="" disabled selected>-- Pilih Metode Pembayaran --
                                                </option>
                                                @foreach ($paymentMethods as $method)
                                                    <option value="{{ $method->id }}"
                                                        {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>
                                                        {{ $method->method_name ?? 'N/A' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">
                                                @error('payment_method_id')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="amount_paid">Jumlah Pembayaran <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light">Rp</span>
                                                </div>
                                                <input id="amount_paid" type="text"
                                                    class="form-control @error('amount_paid') is-invalid @enderror"
                                                    name="amount_paid"
                                                    value="{{ formatRupiahPlain(old('amount_paid')) }}"
                                                    placeholder="1000000"
                                                    style="border-start-end-radius: .25rem; border-end-end-radius: .25rem;">
                                                <div class="invalid-feedback" id="price-per-kg-error">
                                                    @error('amount_paid')
                                                        {{ $message }}
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="status">Status Pembayaran <span
                                                    class="text-danger">*</span></label>
                                            <select name="status" id="status"
                                                class="custom-select @error('status') is-invalid @enderror">
                                                <option value="" disabled selected>-- Pilih Status --</option>
                                                <option value="pending"
                                                    {{ old('status') === 'pending' ? 'selected' : '' }}>
                                                    Menunggu
                                                </option>
                                                <option value="success"
                                                    {{ old('status') === 'success' ? 'selected' : '' }}>
                                                    Diterima
                                                </option>
                                                <option value="rejected"
                                                    {{ old('status') === 'rejected' ? 'selected' : '' }}>
                                                    Ditolak
                                                </option>
                                            </select>
                                            <div class="invalid-feedback">
                                                @error('status')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label for="notes">Keterangan</label>
                                            <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                                placeholder="Tulis keterangan singkat...">{{ old('notes') }}</textarea>
                                            <div class="invalid-feedback">
                                                @error('notes')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <hr class="my-4">

                                            @php
                                                $finalServicePrice = 0;

                                                $totalAmountPaid = 0;

                                                foreach ($details as $detail) {
                                                    $finalServicePrice += $detail->final_service_price;
                                                }

                                                foreach ($transactions as $transaction) {
                                                    $totalAmountPaid += $transaction->amount_paid;
                                                }

                                                $finalAmountPaid = $finalServicePrice + $order->delivery_cost;

                                                $remainingPayment = $finalAmountPaid - $totalAmountPaid;
                                            @endphp

                                            <div class="row justify-content-end mb-4">
                                                <div class="col-md-6">
                                                    <div
                                                        class="row justify-content-between align-items-center {{ $totalAmountPaid <= 0 ? 'd-none' : '' }}">
                                                        <div class="col-6">Pembayaran Sebelumnya</div>
                                                        <div class="col-6 text-right font-weight-bold">
                                                            {{ formatRupiah($totalAmountPaid) }}
                                                        </div>
                                                    </div>
                                                    <div class="row justify-content-between align-items-center">
                                                        <div class="col-6">Pembayaran Saat Ini</div>
                                                        <div class="col-6 text-right font-weight-bold">
                                                            <span id="additional-paid">{{ formatRupiah(0) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <hr>
                                                        </div>
                                                    </div>
                                                    <div class="row justify-content-between align-items-center">
                                                        <div class="col-6">Jumlah Pembayaran</div>
                                                        <div class="col-6 text-right font-weight-bold">
                                                            <span id="total-amount-paid">
                                                                {{ formatRupiah($totalAmountPaid) ?? '-' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="row justify-content-between align-items-center">
                                                        <div class="col-6">Total Pembayaran</div>
                                                        <div class="col-6 text-right font-weight-bold">
                                                            {{ formatRupiah($finalAmountPaid) ?? '-' }}
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <hr>
                                                        </div>
                                                    </div>
                                                    <div class="row justify-content-between align-items-center">
                                                        <div class="col-6">
                                                            <span id="remaining-payment-label">
                                                                {{ $remainingPayment < 0 ? 'Uang Kembali' : 'Sisa Pembayaran' }}
                                                            </span>
                                                        </div>
                                                        <div class="col-6 text-right font-weight-bold">
                                                            <span id="remaining-payment">
                                                                {{ $remainingPayment < 0 ? formatRupiah(abs($remainingPayment)) : formatRupiah($remainingPayment) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex justify-content-center justify-content-md-end align-items-center"
                                                style="gap: .5rem">
                                                <a href="{{ url("/order/{$order->id}/transaction") }}"
                                                    class="btn btn-secondary">Kembali</a>
                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('modules/select2/dist/js/select2.full.min.js') }}"></script>

    @if ($isCustomer)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selectElement = $('#payment_method_id');

                // Inisialisasi Select2
                selectElement.select2();

                function toggleTypeFields(paymentType) {
                    const allGroups = ['manual', 'online', 'bank_transfer'];

                    allGroups.forEach(group => {
                        const fields = document.querySelectorAll(`.visible-field-${group}`);

                        fields.forEach(field => {
                            const shouldBeVisible = field.classList.contains(
                                `visible-field-${paymentType}`);
                            field.classList.toggle('d-none', !shouldBeVisible);
                            field.querySelectorAll('input, select, textarea').forEach(el => {
                                el.disabled = !shouldBeVisible;
                            });
                        });
                    });
                }

                function updatePaymentPreview(description, imgUrl, imgName) {
                    const descriptionPreview = document.getElementById('paymentDescriptionPreview');
                    const descriptionContainer = document.getElementById('paymentDescription');

                    const imgPreview = document.getElementById('paymenentImgPreview');
                    const imgElement = document.getElementById('paymenentImg');

                    // Tampilkan atau sembunyikan deskripsi
                    if (description && description !== 'empty_payment_description') {
                        descriptionPreview.classList.remove('d-none');
                        descriptionContainer.innerHTML = description;
                    } else {
                        descriptionPreview.classList.add('d-none');
                        descriptionContainer.innerHTML = '';
                    }

                    // Tampilkan atau sembunyikan gambar
                    if (imgUrl && imgUrl !== 'empty_payment_img') {
                        imgPreview.classList.remove('d-none');
                        imgElement.src = imgUrl;
                        imgElement.alt = imgName || 'Gambar metode pembayaran';
                    } else {
                        imgPreview.classList.add('d-none');
                        imgElement.src = '';
                        imgElement.alt = '';
                    }
                }

                function handleChange(option) {
                    const paymentType = option.getAttribute('data-payment-type');
                    const paymentDescription = option.getAttribute('data-payment-description');
                    const paymentImgUrl = option.getAttribute('data-payment-img-url');
                    const paymentImgName = option.getAttribute('data-payment-img-name');

                    toggleTypeFields(paymentType);
                    updatePaymentPreview(paymentDescription, paymentImgUrl, paymentImgName);
                }

                // Saat nilai select berubah
                selectElement.on('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    handleChange(selectedOption);
                });

                // Jalankan saat pertama kali halaman dimuat jika sudah ada yang dipilih
                const selectedOption = selectElement[0].options[selectElement[0].selectedIndex];
                if (selectedOption && selectedOption.value !== '') {
                    handleChange(selectedOption);
                }
            });
        </script>

        <script>
            // Fungsi untuk menampilkan field
            document.addEventListener('DOMContentLoaded', function() {
                const selectElement = $('#payment_method_id');

                // Inisialisasi Select2
                selectElement.select2();

                function toggleTypeFields(paymentType) {
                    const allGroups = ['manual', 'online', 'bank_transfer'];

                    allGroups.forEach(group => {
                        const fields = document.querySelectorAll(`.visible-field-${group}`);

                        fields.forEach(field => {
                            const shouldBeVisible = field.classList.contains(
                                `visible-field-${paymentType}`);
                            field.classList.toggle('d-none', !shouldBeVisible);
                            field.querySelectorAll('input, select, textarea').forEach(el => {
                                el.disabled = !shouldBeVisible;
                            });
                        });
                    });
                }

                // Saat nilai select berubah
                selectElement.on('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const paymentType = selectedOption.getAttribute('data-payment-type');
                    const paymentDescription = selectedOption.getAttribute('data-payment-description');
                    const paymentImg = selectedOption.getAttribute('data-payment-img');
                    toggleTypeFields(paymentType);
                });

                // Jalankan saat pertama kali halaman dimuat
                const selectedOption = selectElement[0].options[selectElement[0].selectedIndex];
                const initialPaymentType = selectedOption.getAttribute('data-payment-type');
                const initialPaymentDescription = selectedOption.getAttribute('data-payment-description');
                const initialPaymentImg = selectedOption.getAttribute('data-payment-img');
                toggleTypeFields(initialPaymentType);
            });

            // ======== VALIDASI INPUTAN GAMBAR ========
            document.getElementById('img').addEventListener('change', function(e) {
                const file = e.target.files[0];
                const fieldId = document.getElementById('img');
                const previewContainer = document.getElementById('img-preview');
                const previewImage = previewContainer.querySelector('img');
                const fileName = document.getElementById('filename');
                const errorContainer = document.getElementById('img-preview-error');

                errorContainer.textContent = '';

                fieldId.classList.remove('is-invalid');

                if (file) {
                    const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
                    const maxSize = 2 * 1024 * 1024; // 2MB

                    if (!validTypes.includes(file.type)) {
                        fieldId.classList.add('is-invalid');
                        errorContainer.textContent = 'Gambar hanya boleh berformat PNG, JPG, JPEG, atau WEBP.';
                        return;
                    }

                    if (file.size > maxSize) {
                        fieldId.classList.add('is-invalid');
                        errorContainer.textContent = 'Ukuran maksimum gambar adalah 2MB.';
                        return;
                    }

                    fileName.textContent = file.name;

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        previewImage.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        </script>
    @else
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const totalAmountPaid = {{ formatRupiahPlain($totalAmountPaid) }};
                const finalAmountToPay =
                    {{ formatRupiahPlain($finalServicePrice) + formatRupiahPlain($order->delivery_cost) }};

                const amountPaidInput = document.getElementById('amount_paid');
                const totalAmountPaidDisplay = document.getElementById('total-amount-paid');
                const additionalPaidDisplay = document.getElementById('additional-paid');
                const remainingOrChangeLabel = document.getElementById('remaining-payment-label');
                const remainingOrChangeDisplay = document.getElementById('remaining-payment');

                function formatToRupiah(number) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(number);
                }

                amountPaidInput.addEventListener('input', function() {
                    const inputValue = amountPaidInput.value.replace(/\D/g, '');
                    const newAmountPaid = parseInt(inputValue || '0');

                    const updatedTotalAmountPaid = totalAmountPaid + newAmountPaid;
                    const remainingOrChange = finalAmountToPay - updatedTotalAmountPaid;

                    totalAmountPaidDisplay.textContent = formatToRupiah(updatedTotalAmountPaid);
                    additionalPaidDisplay.textContent = formatToRupiah(newAmountPaid);

                    if (remainingOrChange < 0) {
                        remainingOrChangeLabel.textContent = 'Uang Kembali';
                        remainingOrChangeDisplay.textContent = formatToRupiah(Math.abs(remainingOrChange));
                    } else {
                        remainingOrChangeLabel.textContent = 'Sisa Pembayaran';
                        remainingOrChangeDisplay.textContent = formatToRupiah(remainingOrChange);
                    }
                });

                // Trigger initial calculation if field already has value
                if (amountPaidInput.value) {
                    amountPaidInput.dispatchEvent(new Event('input'));
                }
            });
        </script>
    @endif
@endpush

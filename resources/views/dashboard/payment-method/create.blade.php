@extends('layouts.dashboard')

@section('title', 'Tambah Metode Pembayaran')

@push('styles')
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
                            <form action="{{ route('payment-method.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row justify-content-center g-4">
                                    <div class="form-group col-md-6">
                                        <label for="method_name" class="form-label">Metode Pembayaran <span
                                                class="text-danger">*</span></label>
                                        <input id="method_name" type="text"
                                            class="form-control @error('method_name') is-invalid @enderror"
                                            name="method_name" value="{{ old('method_name') }}"
                                            placeholder="Contoh: Cash, BRI(123456789012345)" autofocus>
                                        <div class="invalid-feedback">
                                            @error('method_name')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="payment_type">Tipe Pembayaran <span class="text-danger">*</span></label>
                                        <select name="payment_type" id="payment_type"
                                            class="custom-select @error('payment_type') is-invalid @enderror">
                                            <option value="" disabled selected>-- Pilih Tipe Pembayaran --</option>
                                            <option value="manual" {{ old('payment_type') === 'manual' ? 'selected' : '' }}>
                                                Manual
                                            </option>
                                            <option value="online" {{ old('payment_type') === 'online' ? 'selected' : '' }}>
                                                Online
                                            </option>
                                            <option value="bank_transfer"
                                                {{ old('payment_type') === 'bank_transfer' ? 'selected' : '' }}>
                                                Bank Transfer
                                            </option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('payment_type')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="description">Keterangan</label>
                                        <textarea name="description" id="description" rows="3"
                                            class="form-control @error('description') is-invalid @enderror" placeholder="Tulis keterangan singkat...">{{ old('description') }}</textarea>
                                        <div class="invalid-feedback">
                                            @error('description')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="active">Status <span class="text-danger">*</span></label>
                                        <select name="active" id="active"
                                            class="custom-select @error('active') is-invalid @enderror">
                                            <option value="" disabled selected>-- Pilih Status --</option>
                                            <option value="1" {{ old('active') == '1' ? 'selected' : '' }}>Tersedia
                                            </option>
                                            <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Tidak
                                                Tersedia</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('active')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-9 d-none visible-field-online">
                                        <div class="row justify-content-center align-items-center mb-4 g-4">
                                            <div class="col-sm-6" id="img-preview">
                                                <h6 class="text-small text-center">Pratinjau gambar:</h6>
                                                <!-- style sebagai parameter -->
                                                <div class="ratio ratio-1x1" style="max-width: 360px; margin: auto;">
                                                    <img src="{{ get_image_url('example-image.jpg') }}" alt="New Image"
                                                        loading="lazy" class="rounded d-block w-100 h-100"
                                                        style="object-fit: cover;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6 d-none visible-field-online">
                                        <label for="img" class="mt-3">Gambar (Opsional)</label>
                                        <div class="custom-file">
                                            <input type="file"
                                                class="custom-file-input @error('img') is-invalid @enderror" id="img"
                                                name="img" accept="image/png, image/jpeg, image/jpg, image/webp">
                                            <label class="custom-file-label" for="img"><span id="filename">Pilih
                                                    berkas...</span></label>
                                            <div class="invalid-feedback" id="img-preview-error">
                                                @error('img')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-center justify-content-md-end align-items-center"
                                            style="gap: .5rem">
                                            <a href="{{ url('/payment-method') }}" class="btn btn-secondary">Kembali</a>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fungsi untuk menampilkan field
            function toggleTypeFields(value) {
                const allGroups = ['online'];

                allGroups.forEach(group => {
                    const fields = document.querySelectorAll(`.visible-field-${group}`);
                    const isVisible = (value === group);

                    fields.forEach(field => {
                        field.classList.toggle('d-none', !isVisible);
                        field.querySelectorAll('input, select, textarea').forEach(el => {
                            el.disabled = !isVisible;
                        });
                    });
                });
            }

            // Pasang event listener ke select
            const selectType = document.getElementById('payment_type');
            selectType.addEventListener('change', function() {
                toggleTypeFields(this.value);
            });

            // Jalankan toggle saat halaman pertama kali dimuat dan set default select 
            const selectedValue = selectType.value || '';
            toggleTypeFields(selectedValue);
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
@endpush

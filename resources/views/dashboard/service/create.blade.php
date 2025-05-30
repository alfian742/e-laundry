@extends('layouts.dashboard')

@section('title', 'Tambah Layanan Laundry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/summernote/summernote-bs4.css') }}">
    <style>
        .note-group-select-from-files {
            display: none !important;
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
                            <form action="{{ route('service.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row justify-content-center g-4">
                                    <div class="form-group col-md-4">
                                        <label for="service_name">Nama Layanan <span class="text-danger">*</span></label>
                                        <input id="service_name" type="text"
                                            class="form-control @error('service_name') is-invalid @enderror"
                                            name="service_name" value="{{ old('service_name') }}"
                                            placeholder="Contoh: Cuci+Strika (Per Kilogram)">
                                        <div class="invalid-feedback">
                                            @error('service_name')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="price_per_kg">Harga per (Kg/Item) <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">Rp</span>
                                            </div>
                                            <input id="price_per_kg" type="text"
                                                class="form-control @error('price_per_kg') is-invalid @enderror"
                                                name="price_per_kg" value="{{ formatRupiahPlain(old('price_per_kg')) }}"
                                                placeholder="1000000"
                                                style="border-start-end-radius: .25rem; border-end-end-radius: .25rem;">
                                            <div class="invalid-feedback" id="price-per-kg-error">
                                                @error('price_per_kg')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="active">Status <span class="text-danger">*</span></label>
                                        <select name="active" id="active"
                                            class="custom-select @error('active') is-invalid @enderror">
                                            <option value="" disabled selected>-- Pilih Status --</option>
                                            <option value="1" {{ old('active') == '1' ? 'selected' : '' }}>
                                                Tersedia
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

                                    <div class="form-group col-12">
                                        <label for="description">Deskripsi <span class="text-danger">*</span></label>
                                        <textarea name="description" id="description" rows="3"
                                            class="form-control summernote @error('description') is-invalid @enderror" placeholder="Tulis deskripsi...">{{ old('description') }}</textarea>
                                        <div class="invalid-feedback">
                                            @error('description')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-9">
                                        <div class="row justify-content-center align-items-center mb-4 g-4">
                                            <div class="col-sm-6" id="img-preview">
                                                <h6 class="text-small text-center">Pratinjau gambar:</h6>
                                                <!-- style sebagai parameter -->
                                                <div class="ratio ratio-4x3" style="max-width: 360px; margin: auto;">
                                                    <img src="{{ get_image_url('example-image.jpg') }}" alt="New Image"
                                                        loading="lazy" class="rounded d-block w-100 h-100"
                                                        style="object-fit: cover;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="img" class="mt-3">Gambar (Disarankan rasio 4:3)</label>
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
                                            <a href="{{ url('/service') }}" class="btn btn-secondary">Kembali</a>
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
    <script src="{{ asset('modules/summernote/summernote-bs4.js') }}"></script>
    <script src="{{ asset('modules/summernote/lang/summernote-id-ID.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ======== VALIDASI INPUTAN HARGA ========
            function validateCostInput(inputId, errorId, fieldName) {
                var inputElement = document.getElementById(inputId);
                var errorElement = document.getElementById(errorId);

                inputElement.addEventListener('input', function() {
                    var value = this.value.trim();
                    var isNumeric = /^[0-9]+$/.test(value);

                    if (value !== '' && (isNaN(value) || !isNumeric)) {
                        inputElement.classList.add('is-invalid');
                        errorElement.textContent = fieldName + " tidak valid. Contoh: 10000.";
                    } else {
                        inputElement.classList.remove('is-invalid');
                        errorElement.textContent = "";
                    }
                });
            }

            validateCostInput('price_per_kg', 'price-per-kg-error', 'Harga');
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

@extends('layouts.dashboard')

@section('title', 'Situs')

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
                            <form action="{{ route('site.identity.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row justify-content-center g-4">
                                    <div class="col-12">
                                        <h5 class="card-title">
                                            Identitas Situs
                                        </h5>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="site_name">Nama Situs <span class="text-danger">*</span></label>
                                        <input id="site_name" type="text"
                                            class="form-control @error('site_name') is-invalid @enderror" name="site_name"
                                            value="{{ old('site_name', $site->site_name ?? '') }}" placeholder="Nama situs">
                                        <div class="invalid-feedback">
                                            @error('site_name')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="tagline">Tagline <span class="text-danger">*</span></label>
                                        <input id="tagline" type="text"
                                            class="form-control @error('tagline') is-invalid @enderror" name="tagline"
                                            value="{{ old('tagline', $site->tagline ?? '') }}" placeholder="Slogan bisnis">
                                        <div class="invalid-feedback">
                                            @error('tagline')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-12">
                                        <label for="about_us">Tentang Usaha <span class="text-danger">*</span></label>
                                        <textarea id="about_us" class="form-control summernote @error('about_us') is-invalid @enderror" name="about_us"
                                            rows="4" data-placeholder="Deskripsi usaha">{{ old('about_us', $site->about_us ?? '') }}</textarea>
                                        <div class="invalid-feedback">
                                            @error('about_us')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-12">
                                        <label for="address">Alamat Usaha <span class="text-danger">*</span></label>
                                        <textarea id="address" class="form-control @error('address') is-invalid @enderror" name="address" rows="2"
                                            placeholder="Alamat usaha">{{ old('address', $site->address ?? '') }}</textarea>
                                        <div class="invalid-feedback">
                                            @error('address')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-12">
                                        <label for="operational_hours">Jam Operasional <span
                                                class="text-danger">*</span></label>
                                        <textarea id="operational_hours" class="form-control @error('operational_hours') is-invalid @enderror"
                                            name="operational_hours" rows="2" placeholder="Contoh: Senin - Sabtu, 08.00 - 20.00">{{ old('operational_hours', $site->operational_hours ?? '') }}</textarea>
                                        <div class="invalid-feedback">
                                            @error('operational_hours')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <hr class="mt-4">
                                        <h5 class="card-title">
                                            Kontak & Media Sosial
                                        </h5>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="phone_number">Nomor HP/WA <span class="text-danger">*</span></label>
                                        <input id="phone_number" type="tel"
                                            class="form-control @error('phone_number') is-invalid @enderror"
                                            name="phone_number"
                                            value="{{ old('phone_number', $site->phone_number ?? '') }}"
                                            placeholder="081234567890">
                                        <div class="invalid-feedback" id="phone-number-error">
                                            @error('phone_number')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="email">Email (Opsional)</label>
                                        <input id="email" type="email"
                                            class="form-control @error('email') is-invalid @enderror" name="email"
                                            value="{{ old('email', $site->email ?? '') }}" placeholder="email@example.com">
                                        <div class="invalid-feedback">
                                            @error('email')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="facebook">Facebook (Opsional)</label>
                                        <input id="facebook" type="url"
                                            class="form-control @error('facebook') is-invalid @enderror" name="facebook"
                                            value="{{ old('facebook', $site->facebook ?? '') }}"
                                            placeholder="https://facebook.com/">
                                        <div class="invalid-feedback">
                                            @error('facebook')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="instagram">Instagram (Opsional)</label>
                                        <input id="instagram" type="url"
                                            class="form-control @error('instagram') is-invalid @enderror" name="instagram"
                                            value="{{ old('instagram', $site->instagram ?? '') }}"
                                            placeholder="https://instagram.com/">
                                        <div class="invalid-feedback">
                                            @error('instagram')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="tiktok">TikTok (Opsional)</label>
                                        <input id="tiktok" type="url"
                                            class="form-control @error('tiktok') is-invalid @enderror" name="tiktok"
                                            value="{{ old('tiktok', $site->tiktok ?? '') }}"
                                            placeholder="https://tiktok.com/">
                                        <div class="invalid-feedback">
                                            @error('tiktok')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <hr class="mt-4">
                                        <h5 class="card-title">
                                            Lainnya
                                        </h5>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <div class="row justify-content-center align-items-center m-0">
                                            @if (!empty($site->logo))
                                                <div class="col-sm-6">
                                                    <h6 class="text-small text-center">Logo situs saat ini:</h6>

                                                    <div class="ratio ratio-1x1" style="max-width: 240px; margin: auto;">
                                                        <img src="{{ get_image_url($site->logo) }}" alt="Old Image"
                                                            loading="eager" class="rounded d-block w-100 h-100"
                                                            style="object-fit: cover;">
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-sm-6 d-none" id="img-preview">
                                                <h6 class="text-small text-center">Logo situs baru:</h6>

                                                <div class="ratio ratio-1x1" style="max-width: 240px; margin: auto;">
                                                    <img src="{{ get_image_url('example-image.jpg') }}" alt="Old Image"
                                                        loading="eager" class="rounded d-block w-100 h-100"
                                                        style="object-fit: cover;">
                                                </div>
                                            </div>
                                        </div>

                                        <label for="logo" class="mt-3">Logo Situs (Disarankan rasio 1:1)</label>
                                        <div class="custom-file">
                                            <input type="file"
                                                class="custom-file-input @error('logo') is-invalid @enderror"
                                                id="logo" name="logo"
                                                accept="image/png, image/jpeg, image/jpg, image/webp">
                                            <label class="custom-file-label" for="logo"><span id="filename">Pilih
                                                    berkas...</span></label>
                                            <div class="invalid-feedback" id="img-preview-error">
                                                @error('logo')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex flex-wrap justify-content-center justify-content-sm-between align-items-center"
                                            style="gap: 1.5rem">
                                            <p class="text-lead text-center text-sm-right mb-0" style="gap: .25rem">
                                                <span>Terakhir diperbarui:</span>
                                                <span data-toggle="tooltip"
                                                    title="{{ carbon_format_date($site->updated_at, 'datetime') . ' ' . $zone }}">
                                                    {{ carbon_format_date($site->updated_at, 'human') }}
                                                </span>
                                            </p>

                                            <div class="d-flex align-items-center" style="gap: .5rem">
                                                <a href="{{ url('/dashboard') }}" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                            </div>
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
            // ======== VALIDASI NOMOR HP ========
            var phoneNumberInput = document.getElementById('phone_number');
            var phoneNumberError = document.getElementById('phone-number-error');
            phoneNumberInput.addEventListener('input', function() {
                var value = this.value.trim();
                var isNumeric = /^[0-9]+$/.test(value);

                if (!isNumeric) {
                    phoneNumberInput.classList.add('is-invalid');
                    phoneNumberError.textContent = "Nomor HP/WA harus berupa angka.";
                } else if (!value.startsWith('08')) {
                    phoneNumberInput.classList.add('is-invalid');
                    phoneNumberError.textContent = "Nomor HP/WA tidak valid. Contoh: 081234567890.";
                } else if (value.length < 10 || value.length > 15) {
                    phoneNumberInput.classList.add('is-invalid');
                    phoneNumberError.textContent = "Nomor HP/WA harus antara 10 hingga 15 digit.";
                } else {
                    phoneNumberInput.classList.remove('is-invalid');
                    phoneNumberError.textContent = "";
                }
            });
        });

        // ======== VALIDASI LOGO ========
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const fieldId = document.getElementById('logo');
            const previewContainer = document.getElementById('img-preview');
            const previewImage = previewContainer.querySelector('img');
            const fileName = document.getElementById('filename');
            const errorContainer = document.getElementById('img-preview-error');

            errorContainer.textContent = '';
            previewContainer.classList.add('d-none');

            fieldId.classList.remove('is-invalid');

            if (file) {
                const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
                const maxSize = 2 * 1024 * 1024; // 2MB

                if (!validTypes.includes(file.type)) {
                    fieldId.classList.add('is-invalid');
                    errorContainer.textContent = 'Logo hanya boleh berformat PNG, JPG, JPEG, atau WEBP.';
                    return;
                }

                if (file.size > maxSize) {
                    fieldId.classList.add('is-invalid');
                    errorContainer.textContent = 'Ukuran maksimum logo adalah 2MB.';
                    return;
                }

                fileName.textContent = file.name;

                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImage.src = event.target.result;
                    previewContainer.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
@endpush

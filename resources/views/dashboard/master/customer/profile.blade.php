@extends('layouts.dashboard')

@section('title', 'Profil')

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
                            <form action="{{ route('customer.profile.update', $customer->id) }}" method="POST">
                                @csrf
                                @method('put')
                                <div class="row g-4">
                                    <div class="form-group col-md-6">
                                        <label for="fullname" class="form-label">Nama Lengkap <span
                                                class="text-danger">*</span></label>
                                        <input id="fullname" type="text"
                                            class="form-control @error('fullname') is-invalid @enderror" name="fullname"
                                            value="{{ old('fullname', $customer->fullname) }}" placeholder="Jane Doe">
                                        <div class="invalid-feedback">
                                            @error('fullname')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="email" class="form-label">Email <span
                                                class="text-danger">*</span></label>
                                        <input id="email" type="email"
                                            class="form-control @error('email') is-invalid @enderror" name="email"
                                            value="{{ old('email', $user->email) }}" placeholder="email@example.com"
                                            autocomplete="email">
                                        <div class="invalid-feedback">
                                            @error('email')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="phone_number">Nomor HP/WA (AKTIF) <span
                                                class="text-danger">*</span></label>
                                        <input id="phone_number" type="tel"
                                            class="form-control @error('phone_number') is-invalid @enderror"
                                            name="phone_number" value="{{ old('phone_number', $customer->phone_number) }}"
                                            placeholder="081234567890">
                                        <div class="invalid-feedback" id="phone-number-error">
                                            @error('phone_number')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="address">Alamat <span class="text-danger">*</span></label>
                                        <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="5"
                                            placeholder="Jl. Merdeka, No. 123, Jakarta">{{ old('address', $customer->address) }}</textarea>
                                        <div class="invalid-feedback">
                                            @error('address')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <hr class="mt-4">
                                        <p class="text-lead"><span class="text-danger">*</span> Silakan isi kolom berikut
                                            jika Anda ingin memperbarui kata sandi.</p>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="password_old" class="d-block">Kata Sandi Lama</label>
                                        <input id="password_old" type="password"
                                            class="form-control @error('password_old') is-invalid @enderror"
                                            name="password_old">
                                        <div class="invalid-feedback">
                                            @error('password_old')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group
                                            col-md-4">
                                        <label for="password" class="d-block">Kata Sandi Baru</label>
                                        <input id="password" type="password"
                                            class="form-control @error('password') is-invalid @enderror" name="password"
                                            disabled>
                                        <div class="invalid-feedback" id="password-error">
                                            @error('password')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="password_confirmation" class="d-block">Konfirmasi Kata Sandi
                                            Baru</label>
                                        <input id="password_confirmation" type="password"
                                            class="form-control @error('password_confirmation') is-invalid @enderror"
                                            name="password_confirmation" disabled>
                                        <div class="invalid-feedback" id="password-confirmation-error">
                                            @error('password_confirmation')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-center justify-content-md-end align-items-center"
                                            style="gap: .5rem">
                                            <a href="{{ url('/dashboard') }}" class="btn btn-secondary">Batal</a>
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

            // ======== VALIDASI PASSWORD ========
            // Validasi pengisian password
            const passwordOld = document.getElementById('password_old');
            const password = document.getElementById('password');
            const passwordError = document.getElementById('password-error');
            const passwordConfirmation = document.getElementById('password_confirmation');
            const passwordConfirmationError = document.getElementById('password-confirmation-error');

            passwordOld.addEventListener('input', function() {
                const isPasswordOldFilled = passwordOld.value.trim() !== '';

                password.disabled = !isPasswordOldFilled;
                passwordConfirmation.disabled = !isPasswordOldFilled;

                if (isPasswordOldFilled) {
                    password.classList.add('is-invalid');
                    passwordError.textContent = "Kata sandi baru wajib diisi.";
                    password.setAttribute('required', 'required');

                    passwordConfirmation.classList.add('is-invalid');
                    passwordConfirmationError.textContent = "Konfirmasi kata sandi baru wajib diisi.";
                    passwordConfirmation.setAttribute('required', 'required');
                } else {
                    password.classList.remove('is-invalid');
                    passwordError.textContent = '';
                    password.removeAttribute('required');

                    passwordConfirmation.classList.remove('is-invalid');
                    passwordConfirmationError.textContent = '';
                    passwordConfirmation.removeAttribute('required');
                }

                if (!isPasswordOldFilled) {
                    password.value = '';
                    passwordConfirmation.value = '';
                }
            });

            // Validasi password baru
            password.addEventListener('input', function() {
                if (this.value.length < 8) {
                    password.classList.add('is-invalid');
                    passwordError.textContent = "Kata sandi baru minimal 8 karakter.";
                } else {
                    password.classList.remove('is-invalid');
                    passwordError.textContent = ""; // Hapus pesan error
                }
            });

            // Validasi konfirmasi password baru
            if (passwordConfirmation && passwordConfirmationError) {
                passwordConfirmation.addEventListener('input', function() {
                    if (this.value !== password.value) {
                        passwordConfirmation.classList.add('is-invalid');
                        passwordConfirmationError.textContent = "Konfirmasi kata sandi baru tidak sesuai.";
                    } else {
                        passwordConfirmation.classList.remove('is-invalid');
                        passwordConfirmationError.textContent = ""; // Hapus pesan error
                    }
                });
            }
        });
    </script>
@endpush

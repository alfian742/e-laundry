@extends('layouts.auth')

@section('title', 'Registrasi')

@push('styles')
@endpush

@section('main')
    <form method="POST" action="{{ route('auth.register') }}">
        @csrf

        <div class="form-group">
            <label for="fullname" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
            <input id="fullname" type="text" class="form-control @error('fullname') is-invalid @enderror" name="fullname"
                value="{{ old('fullname') }}" placeholder="Jane Doe" autofocus>
            <div class="invalid-feedback">
                @error('fullname')
                    {{ $message }}
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                value="{{ old('email') }}" placeholder="email@example.com" autocomplete="email">
            <div class="invalid-feedback">
                @error('email')
                    {{ $message }}
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="phone_number">Nomor HP/WA (AKTIF) <span class="text-danger">*</span></label>
            <input id="phone_number" type="tel" class="form-control @error('phone_number') is-invalid @enderror"
                name="phone_number" value="{{ old('phone_number') }}" placeholder="081234567890">
            <div class="invalid-feedback" id="phone-number-error">
                @error('phone_number')
                    {{ $message }}
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Kata Sandi <span class="text-danger">*</span></label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                name="password">
            <div class="invalid-feedback" id="password-error">
                @error('password')
                    {{ $message }}
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi <span
                    class="text-danger">*</span></label>
            <input id="password_confirmation" type="password"
                class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation">
            <div class="invalid-feedback" id="password-confirmation-error">
                @error('password_confirmation')
                    {{ $message }}
                @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 mb-4">Registrasi</button>

        <div class="text-center">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validasi Nomor HP
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

            // Validasi Password
            var passwordInput = document.getElementById('password');
            var passwordError = document.getElementById('password-error');
            passwordInput.addEventListener('input', function() {
                if (this.value.length < 8) {
                    passwordInput.classList.add('is-invalid');
                    passwordError.textContent = "Kata sandi minimal 8 karakter.";
                } else {
                    passwordInput.classList.remove('is-invalid');
                    passwordError.textContent = "";
                }
            });

            // Validasi Konfirmasi Password
            var passwordConfirmationInput = document.getElementById('password_confirmation');
            var passwordConfirmationError = document.getElementById('password-confirmation-error');

            if (passwordConfirmationInput && passwordConfirmationError) {
                passwordConfirmationInput.addEventListener('input', function() {
                    if (this.value !== passwordInput.value) {
                        passwordConfirmationInput.classList.add('is-invalid');
                        passwordConfirmationError.textContent = "Konfirmasi kata sandi tidak sesuai.";
                    } else {
                        passwordConfirmationInput.classList.remove('is-invalid');
                        passwordConfirmationError.textContent = "";
                    }
                });
            }
        });
    </script>
@endpush

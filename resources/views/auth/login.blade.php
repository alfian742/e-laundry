@extends('layouts.auth')

@section('title', 'Masuk')

@push('styles')
@endpush

@section('main')
    <form method="POST" action="{{ route('auth.login') }}">
        @csrf
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                placeholder="email@example.com" autofocus autocomplete="email">
            <div class="invalid-feedback">
                @error('email')
                    {{ $message }}
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Kata Sandi</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                name="password">
            <div class="invalid-feedback">
                @error('password')
                    {{ $message }}
                @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 mb-4">Masuk</button>
        <div class="text-center">Belum punya akun? <a href="{{ route('register') }}">Registrasi</a></div>
    </form>
@endsection

@push('scripts')
@endpush

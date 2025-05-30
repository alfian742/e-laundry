@extends('layouts.dashboard')

@section('title', 'Ubah Pelanggan')

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
                            <form action="{{ route('customer.update', $customer->id) }}" method="POST">
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
                                        <label for="customer_type">Tipe Pelanggan <span class="text-danger">*</span></label>
                                        <select name="customer_type" id="customer_type"
                                            class="custom-select @error('customer_type') is-invalid @enderror">
                                            <option value="" selected disabled>-- Pilih Tipe Pelanggan --
                                            </option>
                                            <option value="member"
                                                {{ old('customer_type', $customer->customer_type) === 'member' ? 'selected' : '' }}>
                                                Member
                                            </option>
                                            <option value="non_member"
                                                {{ old('customer_type', $customer->customer_type) === 'non_member' ? 'selected' : '' }}>
                                                Non-Member
                                            </option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('customer_type')
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
                                        <div class="d-flex justify-content-center justify-content-md-end align-items-center"
                                            style="gap: .5rem">
                                            <a href="{{ url('/customer') }}" class="btn btn-secondary">Kembali</a>
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
        });
    </script>
@endpush

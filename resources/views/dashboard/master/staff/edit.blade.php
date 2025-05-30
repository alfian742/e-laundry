@extends('layouts.dashboard')

@section('title', 'Ubah Staf')

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
                            <form action="{{ route('staff.update', $staff->id) }}" method="POST">
                                @csrf
                                @method('put')
                                <div class="row g-4">
                                    <div class="form-group col-md-6">
                                        <label for="fullname" class="form-label">Nama Lengkap <span
                                                class="text-danger">*</span></label>
                                        <input id="fullname" type="text"
                                            class="form-control @error('fullname') is-invalid @enderror" name="fullname"
                                            value="{{ old('fullname', $staff->fullname) }}" placeholder="Jane Doe">
                                        <div class="invalid-feedback">
                                            @error('fullname')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="position">Jabatan <span class="text-danger">*</span></label>
                                        <select name="position" id="position"
                                            class="custom-select @error('position') is-invalid @enderror">
                                            <option value="" selected disabled>-- Pilih Jabatan --
                                            </option>
                                            @if ($staff->position === 'owner')
                                                <option value="owner"
                                                    {{ old('position', $staff->position) === 'owner' ? 'selected' : '' }}>
                                                    Pemilik
                                                </option>
                                            @else
                                                <option value="admin"
                                                    {{ old('position', $staff->position) === 'admin' ? 'selected' : '' }}>
                                                    Admin
                                                </option>
                                                <option value="employee"
                                                    {{ old('position', $staff->position) === 'employee' ? 'selected' : '' }}>
                                                    Karyawan
                                                </option>
                                            @endif
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('position')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="phone_number">Nomor HP/WA (AKTIF) <span
                                                class="text-danger">*</span></label>
                                        <input id="phone_number" type="tel"
                                            class="form-control @error('phone_number') is-invalid @enderror"
                                            name="phone_number" value="{{ old('phone_number', $staff->phone_number) }}"
                                            placeholder="081234567890">
                                        <div class="invalid-feedback" id="phone-number-error">
                                            @error('phone_number')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="base_salary">Gaji Pokok <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">Rp</span>
                                            </div>
                                            <input id="base_salary" type="text"
                                                class="form-control @error('base_salary') is-invalid @enderror"
                                                name="base_salary"
                                                value="{{ old('base_salary', formatRupiahPlain($staff->base_salary)) }}"
                                                placeholder="1000000">
                                            <div class="invalid-feedback" id="base-salary-error">
                                                @error('base_salary')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="bonus_salary">Bonus Gaji (Opsional)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">Rp</span>
                                            </div>
                                            <input id="bonus_salary" type="text"
                                                class="form-control @error('bonus_salary') is-invalid @enderror"
                                                name="bonus_salary"
                                                value="{{ old('bonus_salary', formatRupiahPlain($staff->bonus_salary)) }}"
                                                placeholder="1000000">
                                            <div class="invalid-feedback" id="bonus-salary-error">
                                                @error('bonus_salary')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="deductions_salary">Potongan Gaji (Opsional)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">Rp</span>
                                            </div>
                                            <input id="deductions_salary" type="text"
                                                class="form-control @error('deductions_salary') is-invalid @enderror"
                                                name="deductions_salary"
                                                value="{{ old('deductions_salary', formatRupiahPlain($staff->deductions_salary)) }}"
                                                placeholder="1000000">
                                            <div class="invalid-feedback" id="deductions-salary-error">
                                                @error('deductions_salary')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="address">Alamat <span class="text-danger">*</span></label>
                                        <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="5"
                                            placeholder="Jl. Merdeka, No. 123, Jakarta">{{ old('address', $staff->address) }}</textarea>
                                        <div class="invalid-feedback">
                                            @error('address')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-center justify-content-md-end align-items-center"
                                            style="gap: .5rem">
                                            <a href="{{ url('/staff') }}" class="btn btn-secondary">Kembali</a>
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

            // ======== VALIDASI INPUTAN GAJI ========
            function validateSalaryInput(inputId, errorId, fieldName) {
                var inputElement = document.getElementById(inputId);
                var errorElement = document.getElementById(errorId);

                inputElement.addEventListener('input', function() {
                    var value = this.value.trim();
                    var isNumeric = /^[0-9]+$/.test(value);

                    if (value !== '' && (isNaN(value) || !isNumeric)) {
                        inputElement.classList.add('is-invalid');
                        errorElement.textContent = fieldName + " tidak valid. Contoh: 1000000.";
                    } else {
                        inputElement.classList.remove('is-invalid');
                        errorElement.textContent = "";
                    }
                });
            }

            validateSalaryInput('base_salary', 'base-salary-error', 'Gaji pokok');
            validateSalaryInput('bonus_salary', 'bonus-salary-error', 'Bonus gaji');
            validateSalaryInput('deductions_salary', 'deductions-salary-error', 'Potongan gaji');
        });
    </script>
@endpush

@extends('layouts.dashboard')

@section('title', 'Tambah Akun')

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/select2/dist/css/select2-bootstrap4.min.css') }}">
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
                            <form action="{{ route('account.store') }}" method="POST">
                                @csrf
                                <div class="row g-4">
                                    <div class="form-group col-md-6">
                                        <label for="account_type">Tipe Akun <span class="text-danger">*</span></label>
                                        <select name="account_type" id="account_type"
                                            class="custom-select @error('account_type') is-invalid @enderror">
                                            <option value="staff"
                                                {{ old('account_type') === 'staff' || old('account_type') === '' ? 'selected' : '' }}>
                                                Staf</option>
                                            <option value="customer"
                                                {{ old('account_type') === 'customer' ? 'selected' : '' }}>Pelanggan
                                                (Member)</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('account_type')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>


                                    <div class="form-group col-md-6 visible-field-staff d-none">
                                        <label for="staff_id">Nama Staf <span class="text-danger">*</span></label>
                                        <select name="staff_id" id="staff_id"
                                            class="custom-select select2 @error('staff_id') is-invalid @enderror">
                                            <option value="" selected disabled>-- Pilih Nama Staf --</option>
                                            @foreach ($staffs as $staff)
                                                <option value="{{ $staff->id }}" data-role="{{ $staff->position }}"
                                                    {{ old('staff_id') === $staff->id ? 'selected' : '' }}>
                                                    {{ $staff->fullname }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('staff_id')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6 visible-field-customer d-none">
                                        <label for="customer_id">Nama Pelanggan (Member) <span
                                                class="text-danger">*</span></label>
                                        <select name="customer_id" id="customer_id"
                                            class="custom-select select2 @error('customer_id') is-invalid @enderror">
                                            <option value="" selected disabled>-- Pilih Nama Pelanggan (Member) --
                                            </option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}" data-role="customer"
                                                    {{ old('customer_id') === $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->fullname }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('customer_id')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="email" class="form-label">Email <span
                                                class="text-danger">*</span></label>
                                        <input id="email" type="email"
                                            class="form-control @error('email') is-invalid @enderror" name="email"
                                            value="{{ old('email') }}" placeholder="email@example.com"
                                            autocomplete="email">
                                        <div class="invalid-feedback">
                                            @error('email')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="status">Status Akun <span class="text-danger">*</span></label>
                                        <select name="status" id="status"
                                            class="custom-select @error('status') is-invalid @enderror">
                                            <option value="" selected disabled>-- Pilih Status Akun --</option>
                                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Aktif
                                            </option>
                                            <option value="non_active"
                                                {{ old('status') === 'non_active' ? 'selected' : '' }}>Tidak Aktif</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('status')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-center justify-content-md-end align-items-center"
                                            style="gap: .5rem">
                                            <a href="{{ url('/account') }}" class="btn btn-secondary">Kembali</a>
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
    <script src="{{ asset('modules/select2/dist/js/select2.full.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fungsi untuk menampilkan field berdasarkan account_type
            function togglAccountTypeFields(value) {
                const allGroups = ['staff', 'customer'];

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
            const accountTypeSelect = document.getElementById('account_type');
            accountTypeSelect.addEventListener('change', function() {
                togglAccountTypeFields(this.value);
            });

            // Jalankan toggle saat halaman pertama kali dimuat dan set default select ke "staff"
            const selectedValue = accountTypeSelect.value || 'staff'; // Default "staff" jika tidak ada pilihan
            togglAccountTypeFields(selectedValue);
        });
    </script>
@endpush

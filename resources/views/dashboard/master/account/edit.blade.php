@extends('layouts.dashboard')

@section('title', 'Ubah Akun')

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
                            <form action="{{ route('account.update', $account->id) }}" method="POST">
                                @csrf
                                @method('put')
                                <div class="row g-4">
                                    <div class="form-group col-md-4">
                                        <label for="fullname" class="form-label">Nama Lengkap
                                            {{ !is_null($account->staff_id) && $account->relatedStaff ? 'Staf' : 'Pelanggan (Member)' }}
                                            <span class="text-danger">*</span></label>
                                        <input id="fullname" type="text"
                                            class="form-control @error('fullname') is-invalid @enderror" name="fullname"
                                            value="{{ old('fullname', !is_null($account->staff_id) && $account->relatedStaff ? $account->relatedStaff->fullname : $account->relatedCustomer->fullname) }}"
                                            placeholder="Jane Doe" disabled>
                                        <div class="invalid-feedback">
                                            @error('fullname')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="email" class="form-label">Email <span
                                                class="text-danger">*</span></label>
                                        <input id="email" type="email"
                                            class="form-control @error('email') is-invalid @enderror" name="email"
                                            value="{{ old('email', $account->email) }}" placeholder="email@example.com"
                                            autocomplete="email">
                                        <div class="invalid-feedback">
                                            @error('email')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label for="status">Status Akun <span class="text-danger">*</span></label>
                                        <select name="status" id="status"
                                            class="custom-select @error('status') is-invalid @enderror">
                                            <option value="" selected disabled>-- Pilih Status Akun --</option>
                                            <option value="active"
                                                {{ old('status', $account->status) === 'active' ? 'selected' : '' }}>Aktif
                                            </option>
                                            <option value="non_active"
                                                {{ old('status', $account->status) === 'non_active' ? 'selected' : '' }}>
                                                Tidak Aktif</option>
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
@endpush

@extends('layouts.dashboard')

@section('title', 'Tambah Metode Antar/Jemput')

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
                            <form action="{{ route('delivery-method.store') }}" method="POST">
                                @csrf
                                <div class="row g-4">
                                    <div class="form-group col-md-6">
                                        <label for="method_name" class="form-label">Metode Antar/Jemput <span
                                                class="text-danger">*</span></label>
                                        <input id="method_name" type="text"
                                            class="form-control @error('method_name') is-invalid @enderror"
                                            name="method_name" value="{{ old('method_name') }}"
                                            placeholder="Contoh: Ambil & Antar oleh Kurir" autofocus>
                                        <div class="invalid-feedback">
                                            @error('method_name')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="cost">Biaya Pengiriman <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">Rp</span>
                                            </div>
                                            <input id="cost" type="text"
                                                class="form-control @error('cost') is-invalid @enderror" name="cost"
                                                value="{{ old('cost') }}" placeholder="1000000">
                                            <div class="invalid-feedback" id="cost-error">
                                                @error('cost')
                                                    {{ $message }}
                                                @enderror
                                            </div>
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

                                    <div class="col-12">
                                        <div class="d-flex justify-content-center justify-content-md-end align-items-center"
                                            style="gap: .5rem">
                                            <a href="{{ url('/delivery-method') }}" class="btn btn-secondary">Kembali</a>
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
            // ======== VALIDASI INPUTAN BIAYA PENGIRIMAN ========
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

            validateCostInput('cost', 'cost-error', 'Biaya Pengiriman');
        });
    </script>
@endpush

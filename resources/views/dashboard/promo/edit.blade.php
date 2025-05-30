@extends('layouts.dashboard')

@section('title', 'Ubah Promo')

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
                            <form action="{{ route('promo.update', $promo->id) }}" method="POST">
                                @csrf
                                @method('put')
                                <div class="row g-4">
                                    <div class="form-group col-md-6">
                                        <label for="promo_name" class="form-label">Nama Promo <span
                                                class="text-danger">*</span></label>
                                        <input id="promo_name" type="text"
                                            class="form-control @error('promo_name') is-invalid @enderror" name="promo_name"
                                            value="{{ old('promo_name', $promo->promo_name) }}"
                                            placeholder="Contoh: Promo Hari Raya" autofocus>
                                        <div class="invalid-feedback">
                                            @error('promo_name')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="discount_percent" class="form-label">Diskon <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input id="discount_percent" type="number" step="0.01" min="1"
                                                max="100"
                                                class="form-control @error('discount_percent') is-invalid @enderror"
                                                name="discount_percent"
                                                value="{{ old('discount_percent', intval($promo->discount_percent)) }}"
                                                placeholder="Contoh: 10.5">
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-light"
                                                    style="border-start-end-radius: .25rem; border-end-end-radius: .25rem;">%</span>
                                            </div>
                                            <div class="invalid-feedback">
                                                @error('discount_percent')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="customer_scope">Segmentasi Pelanggan <span
                                                class="text-danger">*</span></label>
                                        <select name="customer_scope" id="customer_scope"
                                            class="custom-select @error('customer_scope') is-invalid @enderror">
                                            <option value="" disabled selected>-- Pilih Segmentasi Pelanggan --
                                            </option>
                                            <option value="member"
                                                {{ old('customer_scope', $promo->customer_scope) === 'member' ? 'selected' : '' }}>
                                                Member
                                            </option>
                                            <option value="non_member"
                                                {{ old('customer_scope', $promo->customer_scope) === 'non_member' ? 'selected' : '' }}>
                                                Non Member</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('customer_scope')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="promo_type">Tipe Promo <span class="text-danger">*</span></label>
                                        <select name="promo_type" id="promo_type"
                                            class="custom-select @error('promo_type') is-invalid @enderror">
                                            <option value="" disabled selected>-- Pilih Tipe Promo --</option>
                                            <option value="daily"
                                                {{ old('promo_type', $promo->promo_type) === 'daily' ? 'selected' : '' }}>
                                                Harian</option>
                                            <option value="date_range"
                                                {{ old('promo_type', $promo->promo_type) === 'date_range' ? 'selected' : '' }}>
                                                Periode
                                            </option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('promo_type')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6 visible-field-daily d-none">
                                        <label for="day_of_week">Hari Promo <span class="text-danger">*</span></label>
                                        <select name="day_of_week" id="day_of_week"
                                            class="custom-select @error('day_of_week') is-invalid @enderror">
                                            <option value="" selected disabled>-- Pilih Hari --</option>
                                            @foreach (['monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu', 'sunday' => 'Minggu'] as $key => $label)
                                                <option value="{{ $key }}"
                                                    {{ old('day_of_week', $promo->day_of_week) === $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('day_of_week')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6 visible-field-date_range d-none">
                                        <label for="start_date">Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date" id="start_date"
                                            class="form-control @error('start_date') is-invalid @enderror"
                                            value="{{ old('start_date', $promo->start_date) }}">
                                        <div class="invalid-feedback">
                                            @error('start_date')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6 visible-field-date_range d-none">
                                        <label for="end_date">Tanggal Selesai <span class="text-danger">*</span></label>
                                        <input type="date" name="end_date" id="end_date"
                                            class="form-control @error('end_date') is-invalid @enderror"
                                            value="{{ old('end_date', $promo->end_date) }}">
                                        <div class="invalid-feedback">
                                            @error('end_date')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="description">Keterangan Promo</label>
                                        <textarea name="description" id="description" rows="3"
                                            class="form-control @error('description') is-invalid @enderror" placeholder="Tulis keterangan singkat...">{{ old('description', $promo->description) }}</textarea>
                                        <div class="invalid-feedback">
                                            @error('description')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="active">Status Promo <span class="text-danger">*</span></label>
                                        <select name="active" id="active"
                                            class="custom-select @error('active') is-invalid @enderror">
                                            <option value="" disabled selected>-- Pilih Status Promo --</option>
                                            <option value="1"
                                                {{ old('active', $promo->active) == '1' ? 'selected' : '' }}>Tersedia
                                            </option>
                                            <option value="0"
                                                {{ old('active', $promo->active) == '0' ? 'selected' : '' }}>Tidak
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
                                            <a href="{{ url('/promo') }}" class="btn btn-secondary">Kembali</a>
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
            // Fungsi untuk menampilkan field berdasarkan promo_type
            function toggleVisibleFields(value) {
                const allGroups = ['daily', 'date_range'];

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
            const valueFromSelect = document.getElementById('promo_type');
            valueFromSelect.addEventListener('change', function() {
                toggleVisibleFields(this.value);
            });

            // Jalankan toggle saat halaman pertama kali dimuat dan set
            const selectedValue = valueFromSelect.value || null;
            toggleVisibleFields(selectedValue);
        });
    </script>
@endpush

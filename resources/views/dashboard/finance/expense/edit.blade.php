@extends('layouts.dashboard')

@section('title', 'Ubah Pengeluaran')

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
                            <form action="{{ route('expense.update', $expense->id) }}" method="POST">
                                @csrf
                                @method('put')
                                <div class="row g-4">
                                    <div class="form-group col-md-6">
                                        <label for="expense_category" class="form-label">Jenis Pengeluaran <span
                                                class="text-danger">*</span></label>
                                        <input id="expense_category" type="text"
                                            class="form-control @error('expense_category') is-invalid @enderror"
                                            name="expense_category"
                                            value="{{ old('expense_category', $expense->expense_category) }}"
                                            placeholder="Contoh: Tagihan listrik, Gaji Karyawan" autofocus>
                                        <div class="invalid-feedback">
                                            @error('expense_category')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="notes">Keterangan (Opsional)</label>
                                        <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                            placeholder="Tulis keterangan...">{{ old('notes', $expense->notes) }}</textarea>
                                        <div class="invalid-feedback">
                                            @error('notes')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="total_amount">Total Tagihan <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">Rp</span>
                                            </div>
                                            <input id="total_amount" type="text"
                                                class="form-control @error('total_amount') is-invalid @enderror"
                                                name="total_amount"
                                                value="{{ formatRupiahPlain(old('total_amount', $expense->total_amount)) }}"
                                                placeholder="1000000">
                                            <div class="invalid-feedback" id="total-amount-error">
                                                @error('total_amount')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="paid_amount">Jumlah Pembayaran <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">Rp</span>
                                            </div>
                                            <input id="paid_amount" type="text"
                                                class="form-control @error('paid_amount') is-invalid @enderror"
                                                name="paid_amount"
                                                value="{{ formatRupiahPlain(old('paid_amount', $expense->paid_amount)) }}"
                                                placeholder="1000000">
                                            <div class="invalid-feedback" id="paid-amount-error">
                                                @error('paid_amount')
                                                    {{ $message }}
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="status">Status <span class="text-danger">*</span></label>
                                        <select class="custom-select @error('status') is-invalid @enderror" name="status"
                                            id="status">
                                            <option value="" disabled selected>-- Pilih Status --</option>
                                            <option value="unpaid"
                                                {{ old('status', $expense->status) == 'unpaid' ? 'selected' : '' }}>
                                                Belum Dibayar
                                            </option>
                                            <option value="partial"
                                                {{ old('status', $expense->status) == 'partial' ? 'selected' : '' }}>
                                                Belum Lunas
                                            </option>
                                            <option value="paid"
                                                {{ old('status', $expense->status) == 'paid' ? 'selected' : '' }}>
                                                Lunas
                                            </option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="paid_at">Tanggal Pembayaran (Kosongkan Jika Belum Dibayar)</label>
                                        <input id="paid_at" type="date"
                                            class="form-control @error('paid_at') is-invalid @enderror" name="paid_at"
                                            value="{{ old('paid_at', $expense->paid_at) }}">
                                        <div class="invalid-feedback">
                                            @error('paid_at')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-center justify-content-md-end align-items-center"
                                            style="gap: .5rem">
                                            <a href="{{ url('/expense') }}" class="btn btn-secondary">Kembali</a>
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
            // ======== VALIDASI INPUTAN BIAYA ========
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

            validateCostInput('total_amount', 'total-amount-error', 'Total Tagihan');
            validateCostInput('paid_amount', 'paid-amount-error', 'Jumlah Pembayaran');
        });
    </script>
@endpush

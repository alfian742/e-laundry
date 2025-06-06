@extends('layouts.dashboard')

@section('title', 'Kelola Promo Layanan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/datatables/dataTables.min.css') }}">

    <style>
        .table-modal,
        .table-main {
            white-space: nowrap !important;
        }

        .table-main tr>* {
            vertical-align: middle;
        }

        .table-modal tr th {
            width: 10rem !important;
        }

        .table-modal tr td:nth-child(2) {
            width: 1rem !important;
        }
    </style>
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

            <div class="row g-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-4" style="gap: .5rem">
                                <a href="{{ url('/promo') }}" class="btn btn-secondary">Kembali</a>
                            </div>

                            <div
                                class="row justify-content-center justify-content-md-between align-items-center border-top m-0 py-3">
                                <div class="col-md-2">
                                    <div class="bg-warning text-white fw-bold py-3 rounded" style="width: 6rem">
                                        <h4 class="d-flex justify-content-center align-items-center h-100 mb-0">
                                            {{ $promo->discount_percent ? intval($promo->discount_percent) . '%' : 'N/A' }}
                                        </h4>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <div class="row justify-content-between">
                                        <div class="col-sm-8">
                                            <div class="py-2 py-md-0 h-100">
                                                <span class="text-small">
                                                    @if ($promo->customer_scope === 'member')
                                                        Member
                                                    @elseif($promo->customer_scope === 'non_member')
                                                        Non-Member
                                                    @else
                                                        N/A
                                                    @endif
                                                </span>
                                                <h6 class="font-weight-bold text-dark mb-0" style="line-height: 1.25rem">
                                                    {{ $promo->promo_name ?? 'N/A' }} <br>
                                                    <span class="text-small">
                                                        @if ($promo->promo_type === 'daily')
                                                            Berlaku setiap hari
                                                            @if ($promo->day_of_week === 'monday')
                                                                Senin
                                                            @elseif ($promo->day_of_week === 'tuesday')
                                                                Selasa
                                                            @elseif ($promo->day_of_week === 'wednesday')
                                                                Rabu
                                                            @elseif ($promo->day_of_week === 'thursday')
                                                                Kamis
                                                            @elseif ($promo->day_of_week === 'friday')
                                                                Jumat
                                                            @elseif ($promo->day_of_week === 'saturday')
                                                                Sabtu
                                                            @elseif ($promo->day_of_week === 'sunday')
                                                                Minggu
                                                            @else
                                                                N/A
                                                            @endif
                                                        @elseif($promo->promo_type === 'date_range')
                                                            Berlaku pada
                                                            {{ carbon_format_date($promo->start_date) }} -
                                                            {{ carbon_format_date($promo->end_date) }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </span>
                                                </h6>
                                                <p class="text-lead mb-0">{{ $promo->description }}</p>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div
                                                class="d-flex justify-content-start justify-content-sm-end align-items-center h-100">
                                                @if ($promo->active)
                                                    @if ($promo->promo_type === 'date_range' && $promo->end_date < date('Y-m-d'))
                                                        <span class="badge badge-danger">Masa Berlaku
                                                            Habis</span>
                                                    @elseif ($promo->promo_type === 'date_range' && $promo->start_date > date('Y-m-d'))
                                                        <span class="badge badge-warning">Belum
                                                            Berlaku</span>
                                                    @else
                                                        <span class="badge badge-success">Tersedia</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-danger">Tidak Tersedia</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('promo.service.store', $promo->id) }}" method="POST" id="form-create">
                                @csrf

                                <div class="table-responsive">
                                    <table class="table-striped table table-main" id="table-1">
                                        <thead>
                                            <tr class="text-center">
                                                <th class="text-left">
                                                    <div class="custom-checkbox custom-control">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="checkbox-service-all">
                                                        <label for="checkbox-service-all"
                                                            class="custom-control-label">&nbsp;</label>
                                                    </div>
                                                </th>
                                                <th>Nama Layanan</th>
                                                <th>Harga per (Kg/Item)</th>
                                                <th>Status Layanan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($services as $service)
                                                <tr>
                                                    <td class="text-left" style="width: 1rem;">
                                                        <div class="custom-checkbox custom-control">
                                                            <input type="checkbox" name="service_ids[]"
                                                                value="{{ $service->id }}"
                                                                class="custom-control-input checkbox-service"
                                                                id="checkbox-service-{{ $service->id }}">
                                                            <label for="checkbox-service-{{ $service->id }}"
                                                                class="custom-control-label">&nbsp;</label>
                                                        </div>
                                                    </td>
                                                    <td>{{ $service->service_name ?? 'N/A' }}</td>
                                                    <td>{{ formatRupiah($service->price_per_kg) ?? '-' }}</td>
                                                    <td class="text-center">
                                                        @if ($service->active)
                                                            <span class="badge badge-success">Tersedia</span>
                                                        @else
                                                            <span class="badge badge-danger">Tidak Tersedia</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-center justify-content-md-end align-items-center mt-5"
                                    style="gap: .5rem">
                                    <button type="submit" class="btn btn-primary" data-toggle="tooltip"
                                        title="Tambah layanan berdasarkan pilihan" id="btn-service">Tambahkan</button>
                                </div>
                            </form>

                            <hr>

                            <div id="pivot-table">
                                <p class="text-lead mb-4">
                                    <span class="text-danger">*</span> Layanan yang ditambahkan untuk promo
                                    <span class="font-weight-bold">{{ $promo->promo_name }}</span>
                                    akan muncul pada tabel dibawah ini.
                                </p>

                                <div class="table-responsive">
                                    <table class="table table-striped table-main" id="promo-service-pivot-table">
                                        <thead>
                                            <tr class="text-center">
                                                <th>
                                                    <div class="custom-checkbox custom-control">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="checkbox-pivot-service-all">
                                                        <label for="checkbox-pivot-service-all"
                                                            class="custom-control-label">&nbsp;</label>
                                                    </div>
                                                </th>
                                                <th>Ditambahkan Pada</th>
                                                <th>Nama Layanan</th>
                                                <th>Harga per (Kg/Item)</th>
                                                <th>Status Layanan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $availableAction = false @endphp

                                            @forelse ($promo->services as $promoService)
                                                @php $availableAction = true @endphp
                                                <tr>
                                                    <td class="text-center" style="width: 1rem;">
                                                        <div class="custom-checkbox custom-control">
                                                            <input type="checkbox" name="service_ids[]"
                                                                value="{{ $promoService->id }}"
                                                                class="custom-control-input checkbox-pivot-service"
                                                                id="checkbox-pivot-service-{{ $promoService->id }}">
                                                            <label for="checkbox-pivot-service-{{ $promoService->id }}"
                                                                class="custom-control-label">&nbsp;</label>
                                                        </div>
                                                    </td>
                                                    <td>{{ carbon_format_date($promoService->pivot->created_at, 'datetime') . " {$zone}" }}
                                                    </td>
                                                    <td>{{ $promoService->service_name ?? 'N/A' }}</td>
                                                    <td>{{ formatRupiah($promoService->price_per_kg) ?? '-' }}</td>
                                                    <td class="text-center">
                                                        @if ($promoService->active)
                                                            <span class="badge badge-success">Tersedia</span>
                                                        @else
                                                            <span class="badge badge-danger">Tidak Tersedia</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">Belum ada layanan yang
                                                        ditambahkan.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if ($availableAction)
                                    <div class="d-flex justify-content-end align-items-center mt-5" style="gap: .5rem">
                                        <button type="button" class="btn btn-danger btn-delete"
                                            data-id="{{ $promo->id }}"
                                            data-action="{{ route('promo.service.destroy', $promo->id) }}"
                                            data-token="{{ csrf_token() }}" data-toggle="tooltip"
                                            title="Hapus layanan berdasarkan pilihan">
                                            Hapus
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('modules/datatables/dataTables.min.js') }}"></script>
    <script src="{{ asset('js/page/modules-datatables.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const checkboxAll = document.getElementById('checkbox-service-all');
            const checkboxes = document.querySelectorAll('.checkbox-service');
            const submitButton = document.getElementById('btn-service');
            const formCreate = document.getElementById('form-create');
            const table = $('#table-1').DataTable();

            // Toggle all checkboxes when "select all" is clicked
            checkboxAll.addEventListener('change', function() {
                const isChecked = checkboxAll.checked;
                // Select all checkboxes in the entire table, not just the current page
                table.$('.checkbox-service').each(function() {
                    this.checked = isChecked;
                });
            });

            // Uncheck "select all" if any checkbox is unchecked
            table.$('.checkbox-service').each(function() {
                this.addEventListener('change', function() {
                    if (!this.checked) {
                        checkboxAll.checked = false;
                    } else {
                        checkboxAll.checked = table.$('.checkbox-service:checked').length === table
                            .$('.checkbox-service').length;
                    }
                });
            });

            // Handle form submission
            formCreate.addEventListener('submit', function(e) {
                const selectedCheckboxes = table.$('.checkbox-service:checked');
                const selectedValues = [];

                // Collect the values of all selected checkboxes across all pages
                selectedCheckboxes.each(function() {
                    selectedValues.push(this.value);
                });

                // Remove duplicate values
                const uniqueValues = [...new Set(selectedValues)];

                // Clear any existing hidden inputs from previous submissions
                formCreate.querySelectorAll('input[name="service_ids[]"]').forEach(input => input
                    .remove());

                // Append the selected values to the form as hidden inputs (unique values only)
                if (uniqueValues.length > 0) {
                    uniqueValues.forEach(function(value) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name =
                            'service_ids[]'; // Same as the name in the original checkboxes
                        input.value = value;
                        formCreate.appendChild(input);
                    });
                } else {
                    e.preventDefault(); // Stop form submission if no item is selected

                    swal({
                        title: 'Peringatan!',
                        text: 'Silakan pilih minimal satu item untuk ditambahkan.',
                        icon: 'warning',
                        buttons: {
                            confirm: {
                                text: 'Oke',
                                visible: true
                            }
                        },
                        closeOnClickOutside: true,
                        closeOnEsc: true
                    }).then(() => {
                        location.reload();
                    });

                    // Auto reload after 5 seconds if no interaction
                    setTimeout(() => {
                        location.reload();
                    }, 5000);

                    // Reload if user clicks outside swal manually
                    document.addEventListener('click', function handleClickOutside() {
                        location.reload();
                        document.removeEventListener('click',
                            handleClickOutside); // Hapus listener supaya tidak reload berkali-kali
                    });
                }
            });
        });

        @if ($availableAction)
            document.addEventListener('DOMContentLoaded', function() {
                const table = document.getElementById('promo-service-pivot-table');
                const selectAll = document.getElementById('checkbox-pivot-service-all');

                if (table && selectAll) {
                    const checkboxes = table.querySelectorAll('.checkbox-pivot-service');

                    selectAll.addEventListener('change', function() {
                        checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    });

                    checkboxes.forEach(cb => {
                        cb.addEventListener('change', function() {
                            const total = checkboxes.length;
                            const checked = table.querySelectorAll(
                                '.checkbox-pivot-service:checked').length;
                            selectAll.checked = (checked === total);
                        });
                    });
                }

                const deleteButton = document.querySelector('.btn-delete');
                if (deleteButton) {
                    deleteButton.addEventListener('click', function(e) {
                        e.preventDefault();

                        const selectedCheckboxes = document.querySelectorAll(
                            '.checkbox-pivot-service:checked');

                        if (selectedCheckboxes.length === 0) {
                            swal({
                                title: 'Peringatan!',
                                text: 'Silakan pilih minimal satu layanan untuk dihapus.',
                                icon: 'warning',
                                button: 'Oke'
                            });
                            return;
                        }

                        swal({
                            title: 'Hapus Data',
                            text: 'Apakah Anda yakin ingin menghapus layanan yang dipilih?',
                            icon: 'warning',
                            buttons: {
                                cancel: 'Batal',
                                confirm: {
                                    text: 'Ya, Hapus!',
                                    value: true,
                                    className: 'btn-danger',
                                }
                            },
                            dangerMode: true,
                        }).then((willDelete) => {
                            if (willDelete) {
                                const deleteForm = document.createElement('form');
                                deleteForm.method = 'POST';
                                deleteForm.action = deleteButton.dataset.action;

                                const csrfInput = document.createElement('input');
                                csrfInput.type = 'hidden';
                                csrfInput.name = '_token';
                                csrfInput.value = deleteButton.dataset.token;

                                const methodInput = document.createElement('input');
                                methodInput.type = 'hidden';
                                methodInput.name = '_method';
                                methodInput.value = 'DELETE';

                                deleteForm.appendChild(csrfInput);
                                deleteForm.appendChild(methodInput);

                                selectedCheckboxes.forEach(cb => {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = 'service_ids[]';
                                    input.value = cb.value;
                                    deleteForm.appendChild(input);
                                });

                                document.body.appendChild(deleteForm);
                                deleteForm.submit();
                            }
                        });
                    });
                }
            });
        @endif
    </script>
@endpush

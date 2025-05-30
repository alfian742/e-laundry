@extends('layouts.dashboard')

@section('title', 'Kelola Promo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/select2/dist/css/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/datatables/dataTables.min.css') }}">

    <style>
        .select2 {
            width: 80% !important;
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
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class=" d-flex flex-wrap justify-content-between align-items-center mb-4"
                                style="gap: .5rem">
                                <a href="{{ url("/service/{$service->id}") }}" class="btn btn-secondary">Kembali</a>

                                <form method="POST"
                                    action="{{ route('service.promo.store', ['service' => $service->id]) }}" class="ml-auto"
                                    style="width: 30rem">
                                    @csrf
                                    <div class="input-group justify-content-center">
                                        <label class="d-none" for="promo_id">&nbsp;</label>
                                        <select name="promo_id" id="promo_id"
                                            class="custom-select select2 @error('promo_id') is-invalid @enderror">
                                            <option value="" selected disabled>
                                                @if ($promos->isEmpty())
                                                    -- Tidak ada promo yang tersedia --
                                                @else
                                                    -- Pilih Nama Promo --
                                                @endif
                                            </option>
                                            @foreach ($promos as $promo)
                                                <option value="{{ $promo->id }}"
                                                    data-promo-name="{{ $promo->promo_name }}"
                                                    data-promo-discount-percent="{{ $promo->discount_percent }}"
                                                    data-promo-active="{{ $promo->active }}"
                                                    data-promo-type="{{ $promo->promo_type }}"
                                                    data-promo-description="{{ $promo->description }}"
                                                    data-promo-customer-scope="{{ $promo->customer_scope }}"
                                                    data-promo-start-date="{{ $promo->start_date }}"
                                                    data-promo-end-date="{{ $promo->end_date }}"
                                                    data-promo-day-of-week="{{ $promo->day_of_week }}"
                                                    {{ old('promo_id') == $promo->id ? 'selected' : '' }}>
                                                    {{ $promo->promo_name ?? 'N/A' }}
                                                    ({{ $promo->discount_percent ? intval($promo->discount_percent) . '%' : 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary"
                                                style="border-start-end-radius: .25rem; border-end-end-radius: .25rem;"
                                                {{ $promos->isEmpty() ? 'disabled' : '' }}>Tambahkan</button>
                                        </div>
                                        <div class="invalid-feedback">
                                            @error('promo_id')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div id="promo-preview" class="d-none">
                                <div
                                    class="row justify-content-center justify-content-md-between align-items-center border-top m-0 pt-3 pb-2">
                                    <div class="col-md-2">
                                        <div id="promo-discount" class="bg-warning text-white fw-bold py-3 rounded"
                                            style="width: 6rem">
                                            <h4 class="d-flex justify-content-center align-items-center h-100 mb-0">0%</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="row justify-content-between">
                                            <div class="col-sm-8">
                                                <div class="py-2 py-md-0 h-100">
                                                    <span id="promo-customer-scope" class="text-small">-</span>
                                                    <h6 class="font-weight-bold text-dark mb-0"
                                                        style="line-height: 1.25rem">
                                                        <span id="promo-name">-</span><br>
                                                        <span id="promo-validity" class="text-small">-</span>
                                                    </h6>
                                                    <p id="promo-description" class="text-lead mb-0">-</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div
                                                    class="d-flex justify-content-start justify-content-sm-end align-items-center h-100">
                                                    <span id="promo-status" class="badge badge-secondary">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <hr>

                                <p class="text-lead mb-0">
                                    <span class="text-danger">*</span> Promo yang ditambahkan untuk layanan
                                    <span class="font-weight-bold">{{ $service->service_name }}</span>
                                    akan muncul pada tabel dibawah ini.
                                </p>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-nowrap table-align-middle" id="table-1">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No.</th>
                                            <th>Ditambahkan Pada</th>
                                            <th>Nama Promo</th>
                                            <th>Diskon (%)</th>
                                            <th>Tipe Promo</th>
                                            <th>Waktu Aktif</th>
                                            <th>Segmentasi</th>
                                            <th>Keterangan</th>
                                            <th>Status Promo</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($promoService as $promo)
                                            <tr>
                                                <td class="text-right">{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ carbon_format_date($promo->pivot->created_at, 'datetime') . " $zone" }}
                                                </td>
                                                <td>{{ $promo->promo_name ?? 'N/A' }}</td>
                                                <td class="text-right">
                                                    {{ $promo->discount_percent ? intval($promo->discount_percent) . '%' : 'N/A' }}
                                                </td>
                                                <td>
                                                    @if ($promo->promo_type === 'daily')
                                                        Harian
                                                    @elseif($promo->promo_type === 'date_range')
                                                        Periode
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($promo->promo_type === 'daily')
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
                                                        {{ carbon_format_date($promo->start_date) }} -
                                                        {{ carbon_format_date($promo->end_date) }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($promo->customer_scope === 'member')
                                                        Member
                                                    @elseif($promo->customer_scope === 'non_member')
                                                        Non-Member
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="text-wrap-overflow">
                                                        {{ $promo->description ?? '-' }}
                                                    </div>
                                                </td>
                                                <td class="text-center">
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
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center" style="gap: .5rem">
                                                        <form
                                                            action="{{ route('service.promo.destroy', ['service' => $service->id, 'promo' => $promo->id]) }}"
                                                            method="POST" id="delete-form-{{ $promo->id }}"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="submit" class="btn btn-danger btn-delete"
                                                                data-toggle="tooltip" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('modules/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('modules/datatables/dataTables.min.js') }}"></script>
    <script src="{{ asset('js/page/modules-datatables.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Gunakan delegasi untuk tombol hapus
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();

                const formId = $(this).closest('form').attr('id');

                swal({
                    title: 'Hapus Promo',
                    text: 'Apakah Anda yakin ingin menghapus promo untuk layanan ini?',
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
                        $('#' + formId).submit();
                    }
                });
            });
        });

        $('#promo_id').on('change', function() {
            const serverNow = new Date('{{ now()->format('Y-m-d H:i:s') }}'); // Waktu sesuai server

            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption.value) return; // Kalau kosong (disabled default)

            document.getElementById('promo-preview').classList.remove('d-none');

            const discountPercent = selectedOption.getAttribute('data-promo-discount-percent') || 0;
            const promoName = selectedOption.getAttribute('data-promo-name') || '-';
            const active = selectedOption.getAttribute('data-promo-active') == 1;
            const promoType = selectedOption.getAttribute('data-promo-type');
            const description = selectedOption.getAttribute('data-promo-description') || '-';
            const customerScope = selectedOption.getAttribute('data-promo-customer-scope');
            const startDate = selectedOption.getAttribute('data-promo-start-date');
            const endDate = selectedOption.getAttribute('data-promo-end-date');
            const dayOfWeek = selectedOption.getAttribute('data-promo-day-of-week');

            // Diskon
            document.querySelector('#promo-discount h4').textContent = (discountPercent ? parseInt(
                discountPercent) + '%' : 'N/A');

            // Nama promo
            document.getElementById('promo-name').textContent = promoName;

            // Scope
            document.getElementById('promo-customer-scope').textContent =
                customerScope === 'member' ? 'Member' :
                customerScope === 'non_member' ? 'Non-Member' : 'N/A';

            // Validity
            if (promoType === 'daily') {
                let day = {
                    'monday': 'Senin',
                    'tuesday': 'Selasa',
                    'wednesday': 'Rabu',
                    'thursday': 'Kamis',
                    'friday': 'Jumat',
                    'saturday': 'Sabtu',
                    'sunday': 'Minggu'
                } [dayOfWeek] || '';
                document.getElementById('promo-validity').textContent = 'Berlaku setiap hari ' + (day ? day :
                    '');
            } else if (promoType === 'date_range') {
                document.getElementById('promo-validity').textContent =
                    `Berlaku pada ${formatDate(startDate)} - ${formatDate(endDate)}`;
            } else {
                document.getElementById('promo-validity').textContent = 'N/A';
            }

            // Description
            document.getElementById('promo-description').textContent = description;

            // Status
            const today = serverNow;
            today.setHours(0, 0, 0, 0); // Set waktu menjadi 00:00:00 untuk mengabaikan perbedaan waktu
            let badge = document.getElementById('promo-status');
            badge.className = 'badge'; // Reset class dulu

            if (!active) {
                badge.classList.add('badge-danger');
                badge.textContent = 'Tidak Tersedia';
            } else if (promoType === 'date_range' && endDate && new Date(endDate).setHours(0, 0, 0, 0) < today) {
                badge.classList.add('badge-danger');
                badge.textContent = 'Masa Berlaku Habis';
            } else if (promoType === 'date_range' && startDate && new Date(startDate).setHours(0, 0, 0, 0) >
                today) {
                badge.classList.add('badge-warning');
                badge.textContent = 'Belum Berlaku';
            } else {
                badge.classList.add('badge-success');
                badge.textContent = 'Tersedia';
            }
        });

        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }

        $(document).ready(function() {
            if ($('#promo_id').val()) {
                $('#promo_id').trigger('change');
            }
        });
    </script>
@endpush

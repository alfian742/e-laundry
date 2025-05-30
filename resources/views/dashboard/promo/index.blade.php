@extends('layouts.dashboard')

@section('title', 'Promo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/datatables/dataTables.min.css') }}">
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
                                <form method="GET" action="{{ route('promo.index') }}" style="width: 30rem">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">Saring</span>
                                        </div>

                                        <!-- Filter Tipe Promo -->
                                        <label class="d-none" for="promo_type">&nbsp;</label>
                                        <select class="custom-select" name="promo_type" id="promo_type">
                                            <option value="all" {{ $promoType === 'all' ? 'selected' : '' }}>Semua Tipe
                                            </option>
                                            <option value="daily" {{ $promoType === 'daily' ? 'selected' : '' }}>Harian
                                            </option>
                                            <option value="date_range" {{ $promoType === 'date_range' ? 'selected' : '' }}>
                                                Periode</option>
                                        </select>

                                        <!-- Filter Segmentasi Pelanggan -->
                                        <label class="d-none" for="customer_scope">&nbsp;</label>
                                        <select class="custom-select" name="customer_scope" id="customer_scope">
                                            <option value="all" {{ $customerScope === 'all' ? 'selected' : '' }}>Semua
                                                Pelanggan</option>
                                            <option value="member" {{ $customerScope === 'member' ? 'selected' : '' }}>
                                                Member</option>
                                            <option value="non_member"
                                                {{ $customerScope === 'non_member' ? 'selected' : '' }}>Non-Member</option>
                                        </select>

                                        <!-- Tombol Tampilkan -->
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">Tampilkan</button>
                                        </div>
                                    </div>
                                </form>

                                @if ($isOwner || $isAdmin)
                                    <a href="{{ url('/promo/create') }}" class="btn btn-primary ml-auto">Tambah</a>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-nowrap table-align-middle" id="table-1">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No.</th>
                                            <th>Dibuat Pada</th>
                                            <th>Nama Promo</th>
                                            <th>Diskon (%)</th>
                                            <th>Tipe Promo</th>
                                            <th>Waktu Aktif</th>
                                            <th>Segmentasi</th>
                                            <th>Keterangan</th>
                                            <th>Status Promo</th>
                                            @if ($isOwner || $isAdmin)
                                                <th>Aksi</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($promos as $promo)
                                            <tr>
                                                <td class="text-right">{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ carbon_format_date($promo->created_at, 'datetime') . " $zone" }}
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
                                                            <span class="badge badge-danger">Masa Berlaku Habis</span>
                                                        @elseif ($promo->promo_type === 'date_range' && $promo->start_date > date('Y-m-d'))
                                                            <span class="badge badge-warning">Belum Berlaku</span>
                                                        @else
                                                            <span class="badge badge-success">Tersedia</span>
                                                        @endif
                                                    @else
                                                        <span class="badge badge-danger">Tidak Tersedia</span>
                                                    @endif
                                                </td>
                                                @if ($isOwner || $isAdmin)
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center" style="gap: .5rem">
                                                            <a href="{{ url("/promo/{$promo->id}/edit") }}"
                                                                class="btn btn-primary" data-toggle="tooltip"
                                                                title="Ubah">
                                                                <i class="fas fa-pencil-alt"></i>
                                                            </a>

                                                            <form action="{{ route('promo.destroy', $promo->id) }}"
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
                                                @endif
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
    <script src="{{ asset('modules/datatables/dataTables.min.js') }}"></script>
    <script src="{{ asset('js/page/modules-datatables.js') }}"></script>

    @if ($isOwner || $isAdmin)
        <script>
            $(document).ready(function() {
                // Gunakan delegasi untuk tombol hapus
                $(document).on('click', '.btn-delete', function(e) {
                    e.preventDefault();

                    const formId = $(this).closest('form').attr('id');

                    swal({
                        title: 'Hapus Data',
                        text: 'Apakah Anda yakin ingin menghapus data ini?',
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
        </script>
    @endif
@endpush

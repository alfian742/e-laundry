@extends('layouts.dashboard')

@section('title', 'Data Staf')

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
                            <div class=" d-flex justify-content-between align-items-center mb-4">
                                <form method="GET" action="{{ route('staff.index') }}" style="width: 13rem">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text bg-light" for="filter">Saring</label>
                                        </div>
                                        <select class="custom-select" name="filter" id="filter"
                                            onchange="this.form.submit()">
                                            <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>Semua
                                            </option>
                                            <option value="admin" {{ $filter === 'admin' ? 'selected' : '' }}>Admin
                                            </option>
                                            <option value="employee" {{ $filter === 'employee' ? 'selected' : '' }}>
                                                Karyawan
                                            </option>
                                        </select>
                                    </div>
                                </form>

                                @if ($isOwner || $isAdmin)
                                    <a href="{{ url('/staff/create') }}" class="btn btn-primary ml-auto">Tambah</a>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-nowrap table-align-middle" id="table-1">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No.</th>
                                            <th>Nama Lengkap</th>
                                            <th>Jabatan</th>
                                            <th>Nomor HP/WA</th>
                                            <th>Status Akun</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($staffs as $staff)
                                            <tr>
                                                <td class="text-right">{{ $loop->iteration }}</td>
                                                <td>{{ $staff->fullname ?? 'N/A' }}</td>
                                                <td>
                                                    @if ($staff->position === 'owner')
                                                        Pemilik
                                                    @elseif($staff->position === 'admin')
                                                        Admin
                                                    @elseif($staff->position === 'employee')
                                                        Karyawan
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    <a href="https://wa.me/{{ formatPhoneNumber($staff->phone_number) }}"
                                                        target="_blank">
                                                        {{ $staff->phone_number ?? 'N/A' }}
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    @if (is_null($staff->userAccount))
                                                        <span class="badge badge-warning">Tidak Ada Akun</span>
                                                    @elseif($staff->userAccount->status === 'active')
                                                        <span class="badge badge-success">Aktif</span>
                                                    @elseif($staff->userAccount->status === 'non_active')
                                                        <span class="badge badge-danger">Tidak Aktif</span>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-center" style="gap: .5rem">
                                                        <a href="{{ url("/staff/{$staff->id}") }}" class="btn btn-info"
                                                            data-toggle="tooltip" title="Detail">
                                                            <i class="fas fa-info-circle"></i>
                                                        </a>

                                                        @if ($isOwner || $isAdmin)
                                                            <a href="{{ url("/staff/{$staff->id}/edit") }}"
                                                                class="btn btn-primary" data-toggle="tooltip"
                                                                title="Ubah">
                                                                <i class="fas fa-pencil"></i>
                                                            </a>

                                                            @if ($staff->position !== 'owner')
                                                                <form action="{{ route('staff.destroy', $staff->id) }}"
                                                                    method="POST" id="delete-form-{{ $staff->id }}"
                                                                    class="d-inline">
                                                                    @method('delete')
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-danger btn-delete"
                                                                        data-toggle="tooltip" title="Hapus">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        @endif
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

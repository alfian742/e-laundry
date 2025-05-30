@extends('layouts.dashboard')

@section('title', 'Data Akun')

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
                                <form method="GET" action="{{ route('account.index') }}" style="width: 13rem">
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
                                            <option value="customer" {{ $filter === 'customer' ? 'selected' : '' }}>
                                                Pelanggan
                                            </option>
                                        </select>
                                    </div>
                                </form>

                                @if ($isOwner || $isAdmin)
                                    <a href="{{ url('/account/create') }}" class="btn btn-primary ml-auto">Tambah</a>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-nowrap table-align-middle" id="table-1">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No.</th>
                                            <th>Nama Lengkap</th>
                                            <th>Peran</th>
                                            <th>Email</th>
                                            <th>Nomor HP/WA</th>
                                            <th>Status Akun</th>
                                            @if ($isOwner || $isAdmin)
                                                <th>Aksi</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($accounts as $account)
                                            <tr>
                                                <td class="text-right">{{ $loop->iteration }}</td>
                                                <td>
                                                    @if ($account->relatedStaff && $account->relatedStaff->fullname)
                                                        <a href="{{ url("/staff/{$account->relatedStaff->id}") }}">
                                                            {{ $account->relatedStaff->fullname ?? 'N/A' }}
                                                        </a>
                                                    @elseif ($account->relatedCustomer && $account->relatedCustomer->fullname)
                                                        <a href="{{ url("/customer/{$account->relatedCustomer->id}") }}">
                                                            {{ $account->relatedCustomer->fullname ?? 'N/A' }}
                                                        </a>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($account->role === 'owner')
                                                        Pemilik
                                                    @elseif($account->role === 'admin')
                                                        Admin
                                                    @elseif($account->role === 'employee')
                                                        Karyawan
                                                    @elseif($account->role === 'customer')
                                                        Pelanggan
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $account->email ?? 'N/A' }}
                                                </td>
                                                <td class="text-right">
                                                    @if ($account->relatedStaff && $account->relatedStaff->phone_number)
                                                        <a href="https://wa.me/{{ formatPhoneNumber($account->relatedStaff->phone_number) }}"
                                                            target="_blank">
                                                            {{ $account->relatedStaff->phone_number ?? 'N/A' }}
                                                        </a>
                                                    @elseif ($account->relatedCustomer && $account->relatedCustomer->phone_number)
                                                        <a href="https://wa.me/{{ formatPhoneNumber($account->relatedCustomer->phone_number) }}"
                                                            target="_blank">
                                                            {{ $account->relatedCustomer->phone_number ?? 'N/A' }}
                                                        </a>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($account->status === 'active')
                                                        <span class="badge badge-success">Aktif</span>
                                                    @elseif($account->status === 'non_active')
                                                        <span class="badge badge-danger">Tidak Aktif</span>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($isOwner || $isAdmin)
                                                        <div class="d-flex justify-content-center" style="gap: .5rem">
                                                            <a href="{{ url("/account/{$account->id}/edit") }}"
                                                                class="btn btn-primary" data-toggle="tooltip"
                                                                title="Ubah">
                                                                <i class="fas fa-pencil"></i>
                                                            </a>

                                                            @if ($account->role !== 'owner')
                                                                <form action="{{ route('account.destroy', $account->id) }}"
                                                                    method="POST" id="delete-form-{{ $account->id }}"
                                                                    class="d-inline">
                                                                    @method('delete')
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-danger btn-delete"
                                                                        data-toggle="tooltip" title="Hapus">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @endif

                                                            <form action="{{ route('account.reset', $account->id) }}"
                                                                method="POST" id="reset-form-{{ $account->id }}"
                                                                class="d-inline">
                                                                @method('put')
                                                                @csrf
                                                                <button type="submit" class="btn btn-warning btn-reset"
                                                                    data-toggle="tooltip" title="Reset Akun">
                                                                    <i class="fas fa-key"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif
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

                // Gunakan delegasi untuk tombol reset
                $(document).on('click', '.btn-reset', function(e) {
                    e.preventDefault();

                    const formId = $(this).closest('form').attr('id');

                    swal({
                        title: 'Reset Akun',
                        text: 'Apakah Anda yakin ingin mereset Akun untuk data ini?',
                        icon: 'warning',
                        buttons: {
                            cancel: 'Batal',
                            confirm: {
                                text: 'Ya, Reset!',
                                value: true,
                                className: 'btn-warning',
                            }
                        },
                        dangerMode: true,
                    }).then((willReset) => {
                        if (willReset) {
                            $('#' + formId).submit();
                        }
                    });
                });
            });
        </script>
    @endif
@endpush

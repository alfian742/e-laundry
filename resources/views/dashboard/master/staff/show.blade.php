@extends('layouts.dashboard')

@section('title', 'Detail Staf')

@push('styles')
    <style>
        .table {
            white-space: nowrap !important;
        }

        .table tr th {
            width: 10rem !important;
        }

        .table tr td:nth-child(2) {
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
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-7">
                                    <h5 class="card-title">Biodata</h5>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th>Nama Lengkap</th>
                                                <td>:</td>
                                                <td>{{ $staff->fullname ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Jabatan</th>
                                                <td>:</td>
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
                                            </tr>
                                            <tr>
                                                <th>Nomor HP/WA</th>
                                                <td>:</td>
                                                <td>
                                                    <a href="https://wa.me/{{ formatPhoneNumber($staff->phone_number) }}"
                                                        target="_blank">
                                                        {{ $staff->phone_number ?? 'N/A' }}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td>:</td>
                                                <td>{{ $staff->userAccount->email ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Status Akun</th>
                                                <td>:</td>
                                                <td>
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
                                            </tr>
                                            <tr>
                                                <th>Alamat</th>
                                                <td>:</td>
                                                <td>{{ $staff->address ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                @if ($isOwner || $isAdmin || Auth::user()->staff_id == $staff->id)
                                    <div class="col-md-5">
                                        <h5 class="card-title">Gaji</h5>

                                        <div class="table-responsive">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <th>Gaji Pokok</th>
                                                    <td>:</td>
                                                    <td>{{ formatRupiah($staff->base_salary) }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Bonus Gaji</th>
                                                    <td>:</td>
                                                    <td>{{ formatRupiah($staff->bonus_salary) }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Potongan Gaji</th>
                                                    <td>:</td>
                                                    <td>{{ formatRupiah($staff->deductions_salary) }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Total Gaji</th>
                                                    <td>:</td>
                                                    <td>{{ formatRupiah($staff->total_salary) }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-12">
                                    <div class="d-flex justify-content-center justify-content-md-end align-items-center mt-4"
                                        style="gap: .5rem">
                                        <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
@endpush

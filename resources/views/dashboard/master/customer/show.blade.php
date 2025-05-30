@extends('layouts.dashboard')

@section('title', 'Detail Pelanggan')

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
                                                <td>{{ $customer->fullname ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Tipe Pelanggan</th>
                                                <td>:</td>
                                                <td>
                                                    @if ($customer->customer_type === 'member')
                                                        Member
                                                    @elseif($customer->customer_type === 'non_member')
                                                        Non-Member
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Nomor HP/WA</th>
                                                <td>:</td>
                                                <td>
                                                    <a href="https://wa.me/{{ formatPhoneNumber($customer->phone_number) }}"
                                                        target="_blank">
                                                        {{ $customer->phone_number ?? 'N/A' }}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td>:</td>
                                                <td>{{ $customer->userAccount->email ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Status Akun</th>
                                                <td>:</td>
                                                <td>
                                                    @if (is_null($customer->userAccount))
                                                        <span class="badge badge-warning">Tidak Ada Akun</span>
                                                    @elseif($customer->userAccount->status === 'active')
                                                        <span class="badge badge-success">Aktif</span>
                                                    @elseif($customer->userAccount->status === 'non_active')
                                                        <span class="badge badge-danger">Tidak Aktif</span>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Alamat</th>
                                                <td>:</td>
                                                <td>{{ $customer->address ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

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

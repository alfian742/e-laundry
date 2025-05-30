@extends('layouts.dashboard')

@section('title', 'Metode Pembayaran')

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
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                @if ($isOwner || $isAdmin)
                                    <a href="{{ url('/payment-method/create') }}" class="btn btn-primary ml-auto">Tambah</a>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-nowrap table-align-middle" id="table-1">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No.</th>
                                            <th>Dibuat Pada</th>
                                            <th>Nama Metode</th>
                                            <th>Tipe Pembayaran</th>
                                            <th>Keterangan</th>
                                            <th>Status</th>
                                            @if ($isOwner || $isAdmin)
                                                <th>Aksi</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($paymentMethods as $method)
                                            <tr>
                                                <td class="text-right">{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ carbon_format_date($method->created_at, 'datetime') . " $zone" }}
                                                </td>
                                                <td>{{ $method->method_name ?? 'N/A' }}</td>
                                                <td class="text-center">
                                                    @if ($method->payment_type === 'manual')
                                                        <span class="badge text-white"
                                                            style="background-color: darksalmon;">Manual</span>
                                                    @elseif ($method->payment_type === 'online')
                                                        <span class="badge text-white"
                                                            style="background-color: slateblue;">Online</span>
                                                    @elseif ($method->payment_type === 'bank_transfer')
                                                        <span class="badge text-white" style="background-color: teal;">Bank
                                                            Transfer</span>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="text-wrap-overflow">
                                                        {{ $method->description ?? '-' }}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if ($method->active)
                                                        <span class="badge badge-success">Tersedia</span>
                                                    @else
                                                        <span class="badge badge-danger">Tidak Tersedia</span>
                                                    @endif
                                                </td>
                                                @if ($isOwner || $isAdmin)
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center" style="gap: .5rem">
                                                            <a href="{{ url("/payment-method/{$method->id}/edit") }}"
                                                                class="btn btn-primary" data-toggle="tooltip"
                                                                title="Ubah">
                                                                <i class="fas fa-pencil-alt"></i>
                                                            </a>

                                                            @php
                                                                $isProtected =
                                                                    ($method->method_name === 'Cash' &&
                                                                        $method->payment_type === 'manual') ||
                                                                    ($method->method_name === 'COD' &&
                                                                        $method->payment_type === 'manual');
                                                            @endphp

                                                            @if (!$isProtected)
                                                                <form
                                                                    action="{{ route('payment-method.destroy', $method->id) }}"
                                                                    method="POST" id="delete-form-{{ $method->id }}"
                                                                    class="d-inline">
                                                                    @csrf
                                                                    @method('delete')
                                                                    <button type="submit" class="btn btn-danger btn-delete"
                                                                        data-toggle="tooltip" title="Hapus">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
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

@extends('layouts.dashboard')

@section('title', 'Layanan Laundry')

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
                                @if ($isOwner || $isAdmin)
                                    <a href="{{ url('/service/create') }}" class="btn btn-primary ml-auto">Tambah</a>
                                @endif
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-nowrap table-align-middle" id="table-1">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Gambar</th>
                                            <th>Dibuat Pada</th>
                                            <th>Nama Layanan</th>
                                            <th>Harga per (Kg/Item)</th>
                                            <th>Promo</th>
                                            <th>Status Layanan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($services as $service)
                                            <tr>
                                                <td>
                                                    <img src="{{ get_image_url($service->img) }}"
                                                        alt="{{ $service->service_name ?? 'N/A' }}"
                                                        loading="{{ $loop->iteration <= 5 ? 'eager' : 'lazy' }}"
                                                        class="rounded d-block mx-auto" style="object-fit: cover"
                                                        width="120" height="120">
                                                </td>
                                                <td>
                                                    {{ carbon_format_date($service->created_at, 'datetime') . " $zone" }}
                                                </td>
                                                <td>{{ $service->service_name ?? 'N/A' }}</td>
                                                <td class="text-right">{{ formatRupiah($service->price_per_kg) }}</td>
                                                <td class="text-center">
                                                    {!! !$service->promos->isEmpty() && $service->promos->where('active', true)->count() > 0
                                                        ? '<span class="badge badge-primary">Ada</span>'
                                                        : '-' !!}
                                                </td>
                                                <td class="text-center">
                                                    @if ($service->active)
                                                        <span class="badge badge-success">Tersedia</span>
                                                    @else
                                                        <span class="badge badge-danger">Tidak Tersedia</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-center" style="gap: .5rem">
                                                        <a href="{{ url("/service/{$service->id}") }}" class="btn btn-info"
                                                            data-toggle="tooltip" title="Detail">
                                                            <i class="fas fa-info-circle"></i>
                                                        </a>
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
@endpush

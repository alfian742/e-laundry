@extends('layouts.dashboard')

@section('title', 'Jadwal Antar/Jemput')

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
                                <form method="GET" action="{{ route('order.schedule') }}" style="width: 18rem">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text bg-light" for="filter">Saring</label>
                                        </div>
                                        <select class="custom-select" name="filter" id="filter"
                                            onchange="this.form.submit()">
                                            <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>Semua
                                                ({{ carbon_format_date(now(), 'month_year') }})</option>
                                            <option value="today" {{ $filter === 'today' ? 'selected' : '' }}>Hari Ini
                                            </option>
                                            <option value="this_week" {{ $filter === 'this_week' ? 'selected' : '' }}>Minggu
                                                Ini</option>
                                        </select>
                                    </div>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-nowrap table-align-middle" id="table-1">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Kode Pesanan</th>
                                            @if (!$isCustomer)
                                                <th>Nama Pelanggan</th>
                                                <th>Tipe Pelanggan</th>
                                                <th>Nomor HP/WA</th>
                                                <th>Alamat</th>
                                            @endif
                                            <th>Metode Antar/Jemput</th>
                                            <th>Waktu Jemput</th>
                                            <th>Waktu Antar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($orders as $order)
                                            @php
                                                $customer = $order?->orderingCustomer;
                                                $delivery = $order?->deliveryOption;

                                                $orderCode = $order->order_code ?? 'N/A';

                                                $fullname =
                                                    $customer && $customer->fullname ? $customer->fullname : 'N/A';
                                                $customerType =
                                                    $customer && $customer->customer_type
                                                        ? ($customer->customer_type === 'member'
                                                            ? 'Member'
                                                            : 'Non-Member')
                                                        : 'N/A';
                                                $phoneNumber =
                                                    $customer && $customer->phone_number
                                                        ? $customer->phone_number
                                                        : 'N/A';
                                                $address = $customer && $customer->address ? $customer->address : 'N/A';
                                            @endphp

                                            <tr>
                                                <td>
                                                    <a href="{{ url("/order/{$order->id}") }}">
                                                        {{ $orderCode }}
                                                    </a>
                                                </td>
                                                @if (!$isCustomer)
                                                    <td>
                                                        @if ($customer)
                                                            <a href="{{ url("/customer/{$customer->id}") }}">
                                                                {{ $fullname }}
                                                            </a>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $customerType }}
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $phoneNumber }}
                                                    </td>
                                                    <td>
                                                        <div class="text-wrap-overflow">
                                                            {{ $address }}
                                                        </div>
                                                    </td>
                                                @endif
                                                <td>
                                                    <div class="text-wrap-overflow">
                                                        {{ $delivery->method_name ?? 'N/A' }}
                                                    </div>
                                                </td>
                                                <td>
                                                    {{ carbon_format_date($order->pickup_date) ?? '' }} -
                                                    {{ $order->pickup_time ? carbon_format_date($order->pickup_time, 'time') . " $zone" : '' }}
                                                </td>
                                                <td>
                                                    {{ carbon_format_date($order->delivery_date) ?? '' }} -
                                                    {{ $order->delivery_time ? carbon_format_date($order->delivery_time, 'time') . " $zone" : '' }}
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

@extends('layouts.dashboard')

@section('title', 'Dashboard')

@push('styles')
    @if (!$isCustomer)
        <link rel="stylesheet" href="{{ asset('modules/select2/dist/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('modules/select2/dist/css/select2-bootstrap4.min.css') }}">
    @endif
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-center justify-content-md-between align-items-center"
                                style="gap: 1.5rem">
                                @php $serverTime = \Carbon\Carbon::now()->timestamp * 1000 @endphp
                                <h6 class="mb-0" id="server-time" data-servertime="{{ $serverTime }}">
                                    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y | HH:mm:ss') }}
                                    {{ $zone }}
                                </h6>

                                <a href="{{ url('/clear-dashboard-cache') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-sync mr-1"></i> Muat Ulang Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                @if (!$isCustomer)
                    <div class="col-lg-4 col-md-4 col-sm-12">
                        <div class="row">
                            <div class="col-12">
                                <div class="card card-statistic-2">
                                    <div class="card-stats border-bottom pb-4">
                                        <div class="card-stats-items justify-content-center mt-4">
                                            <div class="card-stats-item">
                                                <div class="card-stats-item-count" id="order-done-count">0</div>
                                                <div class="card-stats-item-label">Selesai</div>
                                            </div>
                                            <div class="card-stats-item">
                                                <div class="card-stats-item-count" id="order-canceled-count">0</div>
                                                <div class="card-stats-item-label">Dibatalkan</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-icon shadow-primary bg-primary">
                                        <i class="fas fa-dolly"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Pesanan ({{ carbon_format_date(now(), 'month_year') }})</h4>
                                        </div>
                                        <div class="card-body" id="order-total-count">
                                            0
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-6">
                                <div class="card card-statistic-2">
                                    <div class="card-stats border-bottom pb-4">
                                        <div class="card-stats-items justify-content-center mt-4">
                                            <div class="card-stats-item">
                                                <div class="card-stats-item-count" id="staff-owner-count">0</div>
                                                <div class="card-stats-item-label">Pemilik</div>
                                            </div>
                                            <div class="card-stats-item">
                                                <div class="card-stats-item-count" id="staff-admin-count">0</div>
                                                <div class="card-stats-item-label">Admin</div>
                                            </div>
                                            <div class="card-stats-item">
                                                <div class="card-stats-item-count" id="staff-employee-count">0</div>
                                                <div class="card-stats-item-label">Karyawan</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-icon shadow-info bg-info">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Staf</h4>
                                        </div>
                                        <div class="card-body" id="staff-total-count">
                                            0
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-6">
                                <div class="card card-statistic-2">
                                    <div class="card-stats border-bottom pb-4">
                                        <div class="card-stats-items justify-content-center mt-4">
                                            <div class="card-stats-item">
                                                <div class="card-stats-item-count" id="customer-member-count">0</div>
                                                <div class="card-stats-item-label">Member</div>
                                            </div>
                                            <div class="card-stats-item">
                                                <div class="card-stats-item-count" id="customer-non-member-count">0</div>
                                                <div class="card-stats-item-label">Non-Member</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-icon shadow-warning bg-warning">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Pelanggan</h4>
                                        </div>
                                        <div class="card-body" id="customer-total-count">
                                            0
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Ulasan Pelanggan</h4>
                                    </div>
                                    <div class="card-body">
                                        <div id="loading-customer-review-chart" class="text-center">
                                            <img src="{{ asset('img/static/spinner-primary.svg') }}" alt="loading"
                                                class="d-block mx-auto mb-2">
                                            <span>Memuat data...</span>
                                        </div>

                                        <canvas id="customerReviewChart" class="d-none" height="314"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-8 col-sm-12">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="card card-statistic-2">
                                    <div class="card-chart">
                                        <div id="loading-revenue-chart" class="text-center">
                                            <img src="{{ asset('img/static/spinner-primary.svg') }}" alt="loading"
                                                class="d-block mx-auto mb-2">
                                            <span>Memuat data...</span>
                                        </div>

                                        <canvas id="revenue-chart" class="d-none" height="80"></canvas>
                                    </div>
                                    <div class="card-icon shadow-success bg-success">
                                        <i class="fas fa-money-bill-transfer"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Pendapatan ({{ carbon_format_date(now(), 'month_year') }})</h4>
                                        </div>
                                        <div class="card-body">
                                            <span id="revenue_this_month">Rp 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <div class="card card-statistic-2">
                                    <div class="card-chart">
                                        <div id="loading-expense-chart" class="text-center">
                                            <img src="{{ asset('img/static/spinner-primary.svg') }}" alt="loading"
                                                class="d-block mx-auto mb-2">
                                            <span>Memuat data...</span>
                                        </div>

                                        <canvas id="expense-chart" class="d-none" height="80"></canvas>
                                    </div>
                                    <div class="card-icon shadow-danger bg-danger">
                                        <i class="fas fa-money-bill-transfer"></i>
                                    </div>
                                    <div class="card-wrap">
                                        <div class="card-header">
                                            <h4>Pengeluaran ({{ carbon_format_date(now(), 'month_year') }})</h4>
                                        </div>
                                        <div class="card-body">
                                            <span id="expense_this_month">Rp 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Statistik Keuangan</h4>

                                        <div class="card-header-action" style="width: 5rem">
                                            <div class="input-group">
                                                <label class="d-none" for="year">&nbsp;</label>
                                                <select class="custom-select select2" id="year">
                                                    {{-- Diisi melalui JS --}}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="loading-finance-chart" class="text-center">
                                            <img src="{{ asset('img/static/spinner-primary.svg') }}" alt="loading"
                                                class="d-block mx-auto mb-2">
                                            <span>Memuat data...</span>
                                        </div>

                                        <canvas id="financeChart" class="d-none" height="164"></canvas>

                                        <div class="row align-items-center justify-content-between mt-2">
                                            <div class="col-md-6">
                                                <div class="row p-2 align-items-center font-weight-bold">
                                                    <div class="col-2 py-2"
                                                        style="border: 1px solid rgba(32, 201, 151, 1); background-color: rgba(32, 201, 151, 1)">
                                                        &nbsp;
                                                    </div>
                                                    <div class="col-10 py-2 border text-right" id="total_revenue_by_year">
                                                        Rp 0
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row p-2 align-items-center font-weight-bold">
                                                    <div class="col-2 py-2"
                                                        style="border: 1px solid rgba(220, 53, 69, 1); background-color: rgba(220, 53, 69, 1)">
                                                        &nbsp;
                                                    </div>
                                                    <div class="col-10 py-2 border text-right" id="total_expense_by_year">
                                                        Rp 0
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th>Aktivitas Terakhir</th>
                                                        <th>Nama</th>
                                                        <th>Tipe Akun</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($lastActivityUsers as $lastActivity)
                                                        <tr>
                                                            <td>
                                                                <div data-toggle="tooltip"
                                                                    title="{{ carbon_format_date($lastActivity->last, 'datetime') . " {$zone}" }}">
                                                                    {{ carbon_format_date($lastActivity->last, 'human') }}
                                                                </div>
                                                            </td>
                                                            <td>{{ $lastActivity->fullname }}</td>
                                                            <td class="text-center">
                                                                @if ($lastActivity->role === 'owner')
                                                                    Pemilik
                                                                @elseif($lastActivity->role === 'admin')
                                                                    Admin
                                                                @elseif($lastActivity->role === 'employee')
                                                                    Karyawan
                                                                @elseif($lastActivity->role === 'customer')
                                                                    Pelanggan
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center">Tidak aktivitas terbaru
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-12 mb-4 pb-2">
                        <div class="hero text-white hero-bg-image"
                            style="background-image: url('{{ asset('img/static/hero-image.jpg') }}');">
                            <div class="hero-inner">
                                @php
                                    $user = Auth::user();
                                    $fullname = $user?->relatedCustomer?->fullname ?? 'N/A';
                                @endphp

                                <h2>Hai, {{ $fullname }}!</h2>
                                <p class="lead">Selamat datang di layanan laundry {{ $site->site_name }} —
                                    {{ $site->tagline }}</p>
                                <div class="mt-4">
                                    <a href="{{ url('/order/services') }}"
                                        class="btn btn-outline-white btn-lg btn-icon icon-right">Pesan
                                        Layanan <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="card card-statistic-2">
                            <div class="card-stats border-bottom pb-4">
                                <div class="card-stats-title">
                                    Pesanan ({{ carbon_format_date(now(), 'month_year') }})
                                </div>
                                <div class="card-stats-items justify-content-center mt-4">
                                    <div class="card-stats-item">
                                        <div class="card-stats-item-count" id="order-done-count">0</div>
                                        <div class="card-stats-item-label">Selesai</div>
                                    </div>
                                    <div class="card-stats-item">
                                        <div class="card-stats-item-count" id="order-canceled-count">0</div>
                                        <div class="card-stats-item-label">Dibatalkan</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-icon shadow-primary bg-primary">
                                <i class="fas fa-dolly"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total</h4>
                                </div>
                                <div class="card-body" id="order-total-count">
                                    0
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <div class="card card-statistic-2">
                            <div class="card-stats border-bottom pb-4">
                                <div class="card-stats-title">
                                    Pesanan ({{ carbon_format_date(now(), 'month_year') }})
                                </div>
                                <div class="card-stats-items justify-content-center mt-4">
                                    <div class="card-stats-item">
                                        <div class="card-stats-item-count" id="payment-unpaid-count">0</div>
                                        <div class="card-stats-item-label">Belum Dibayar</div>
                                    </div>
                                    <div class="card-stats-item">
                                        <div class="card-stats-item-count" id="payment-partial-count">0</div>
                                        <div class="card-stats-item-label">Belum Lunas</div>
                                    </div>
                                    <div class="card-stats-item">
                                        <div class="card-stats-item-count" id="payment-paid-count">0</div>
                                        <div class="card-stats-item-label">Lunas</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-icon shadow-primary bg-primary">
                                <i class="fas fa-money-bill-transfer"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total</h4>
                                </div>
                                <div class="card-body" id="payment-total-count">
                                    0
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <!-- Page Specific JS File -->
    <script src="{{ asset('modules/chart.min.js') }}"></script>

    @if (!$isCustomer)
        <script src="{{ asset('modules/select2/dist/js/select2.full.min.js') }}"></script>

        <!-- Statistic & Chart -->
        <script>
            Promise.all([
                fetch("{{ route('statistic.order') }}").then(res => res.json()),
                fetch("{{ route('statistic.customer') }}").then(res => res.json()),
                fetch("{{ route('statistic.staff') }}").then(res => res.json()),
            ]).then(([orderData, customerData, staffData]) => {
                // Order
                document.getElementById('order-done-count').textContent = orderData.count_by_status.done;
                document.getElementById('order-canceled-count').textContent = orderData.count_by_status.canceled;
                document.getElementById('order-total-count').textContent = orderData.count_by_status.total;

                // Customer
                document.getElementById('customer-member-count').textContent = customerData.count_by_customer_type
                    .member;
                document.getElementById('customer-non-member-count').textContent = customerData.count_by_customer_type
                    .non_member;
                document.getElementById('customer-total-count').textContent = customerData.count_by_customer_type.total;

                // Staff
                document.getElementById('staff-owner-count').textContent = staffData.count_by_staff_position.owner;
                document.getElementById('staff-admin-count').textContent = staffData.count_by_staff_position.admin;
                document.getElementById('staff-employee-count').textContent = staffData.count_by_staff_position
                    .employee;
                document.getElementById('staff-total-count').textContent = staffData.count_by_staff_position.total;
            });


            Promise.all([
                fetch("{{ route('chart.revenue') }}").then(res => res.json()),
                fetch("{{ route('chart.expense') }}").then(res => res.json()),
                fetch("{{ route('chart.customer.review') }}").then(res => res.json())
            ]).then(([revenueResult, expenseResult, customerReviewResult]) => {
                // ---------------- REVENUE ----------------
                // Sembunyikan chart dan tampilkan loader saat awal
                $('#loading-revenue-chart').removeClass('d-none');
                $('#revenue-chart').addClass('d-none');

                var revenueData = revenueResult.data;
                var totalRevenue = revenueResult.total_revenue_this_month;

                var revenueLabels = revenueData.map(item => item.label);
                var revenueValues = revenueData.map(item => item.amount);

                var revenueChart = document.getElementById("revenue-chart").getContext('2d');
                var revenueChartBg = revenueChart.createLinearGradient(0, 0, 0, 70);
                revenueChartBg.addColorStop(0, 'rgba(40, 167, 69, 0.2)');
                revenueChartBg.addColorStop(1, 'rgba(40, 167, 69, 0)');

                document.getElementById("revenue_this_month").innerText = 'Rp ' + totalRevenue.toLocaleString('id-ID');

                // Hancurkan chart lama jika ada
                if (window.revenueChart) {
                    window.revenueChart.destroy();
                }

                // Inisialisasi chart baru
                window.revenueChart = new Chart(revenueChart, {
                    type: 'line',
                    data: {
                        labels: revenueLabels,
                        datasets: [{
                            label: 'Pendapatan',
                            data: revenueValues,
                            backgroundColor: revenueChartBg,
                            borderWidth: 3,
                            borderColor: 'rgba(32, 201, 151, 1)',
                            pointBorderWidth: 0,
                            pointBorderColor: 'transparent',
                            pointRadius: 3,
                            pointBackgroundColor: 'transparent',
                            pointHoverBackgroundColor: 'rgba(32, 201, 151, 1)',
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                bottom: -1,
                                left: -1
                            }
                        },
                        legend: {
                            display: false
                        },
                        tooltips: {
                            enabled: true,
                            callbacks: {
                                label: function(tooltipItem) {
                                    return 'Rp ' + tooltipItem.yLabel.toLocaleString('id-ID');
                                }
                            }
                        },
                        scales: {
                            yAxes: [{
                                gridLines: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    display: false,
                                    beginAtZero: true
                                }
                            }],
                            xAxes: [{
                                gridLines: {
                                    drawBorder: false,
                                    display: false
                                },
                                ticks: {
                                    display: false
                                }
                            }]
                        }
                    }
                });

                // Tampilkan chart setelah delay
                $('#loading-revenue-chart').addClass('d-none');
                $('#revenue-chart').removeClass('d-none');

                // ---------------- EXPENSE ----------------
                // Sembunyikan chart dan tampilkan loader saat awal
                $('#loading-expense-chart').removeClass('d-none');
                $('#expense-chart').addClass('d-none');

                var expenseData = expenseResult.data;
                var totalExpense = expenseResult.total_expense_this_month;

                var expenseLabels = expenseData.map(item => item.label);
                var expenseValues = expenseData.map(item => item.amount);

                var expenseChart = document.getElementById("expense-chart").getContext('2d');
                var expenseChartBg = expenseChart.createLinearGradient(0, 0, 0, 70);
                expenseChartBg.addColorStop(0, 'rgba(220, 53, 69, 0.2)');
                expenseChartBg.addColorStop(1, 'rgba(220, 53, 69, 0)');

                document.getElementById("expense_this_month").innerText = 'Rp ' + totalExpense.toLocaleString('id-ID');

                // Hancurkan chart lama jika ada
                if (window.expenseChart) {
                    window.expenseChart.destroy();
                }

                // Inisialisasi chart baru
                window.expenseChart = new Chart(expenseChart, {
                    type: 'line',
                    data: {
                        labels: expenseLabels,
                        datasets: [{
                            label: 'Pengeluaran',
                            data: expenseValues,
                            backgroundColor: expenseChartBg,
                            borderWidth: 3,
                            borderColor: 'rgba(220, 53, 69, 1)',
                            pointBorderWidth: 0,
                            pointBorderColor: 'transparent',
                            pointRadius: 3,
                            pointBackgroundColor: 'transparent',
                            pointHoverBackgroundColor: 'rgba(220, 53, 69, 1)',
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                bottom: -1,
                                left: -1
                            }
                        },
                        legend: {
                            display: false
                        },
                        tooltips: {
                            enabled: true,
                            callbacks: {
                                label: function(tooltipItem) {
                                    return 'Rp ' + tooltipItem.yLabel.toLocaleString('id-ID');
                                }
                            }
                        },
                        scales: {
                            yAxes: [{
                                gridLines: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    display: false,
                                    beginAtZero: true
                                }
                            }],
                            xAxes: [{
                                gridLines: {
                                    drawBorder: false,
                                    display: false
                                },
                                ticks: {
                                    display: false
                                }
                            }]
                        }
                    }
                });

                // Tampilkan chart setelah delay
                $('#loading-expense-chart').addClass('d-none');
                $('#expense-chart').removeClass('d-none');

                // ---------------- CUSTOMER REVIEW ----------------
                // Sembunyikan chart dan tampilkan loader saat awal
                $('#loading-customer-review-chart').removeClass('d-none');
                $('#customerReviewChart').addClass('d-none');

                // Pastikan ratings tersedia, jika tidak, inisialisasi dengan nilai nol
                var ratings = customerReviewResult.ratings || {
                    1: 0,
                    2: 0,
                    3: 0,
                    4: 0,
                    5: 0
                };

                // Ambil rating dari 1 sampai 5 secara berurutan
                var labels = ['Bintang 1', 'Bintang 2', 'Bintang 3', 'Bintang 4', 'Bintang 5'];
                var values = [ratings[1] || 0, ratings[2] || 0, ratings[3] || 0, ratings[4] || 0, ratings[5] || 0];

                // Hitung total untuk persentase
                var total = values.reduce((a, b) => a + b, 0);

                // Buat canvas chart
                var customerReviewChartCanvas = document.getElementById("customerReviewChart").getContext('2d');

                // Hancurkan chart lama jika ada
                if (window.customerReviewChartInstance) {
                    window.customerReviewChartInstance.destroy();
                }

                // Inisialisasi chart baru
                window.customerReviewChartInstance = new Chart(customerReviewChartCanvas, {
                    type: 'pie',
                    data: {
                        datasets: [{
                            data: values,
                            backgroundColor: [
                                '#dc3545', // Bintang 1 (merah - jelek)
                                '#fd7e14', // Bintang 2 (oranye - kurang)
                                '#ffc107', // Bintang 3 (kuning - sedang)
                                '#20c997', // Bintang 4 (hijau - bagus)
                                '#007bff' // Bintang 5 (biru - sangat bagus)
                            ],
                            label: 'Ulasan Pengguna'
                        }],
                        labels: labels,
                    },
                    options: {
                        responsive: true,
                        legend: {
                            position: 'bottom',
                        },
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    var dataset = data.datasets[tooltipItem.datasetIndex];
                                    var currentValue = dataset.data[tooltipItem.index];
                                    var percentage = total > 0 ? ((currentValue / total) * 100).toFixed(1) :
                                        0;
                                    return data.labels[tooltipItem.index] + ': ' + currentValue +
                                        ' orang (' + percentage + '%)';
                                }
                            }
                        }
                    },
                });

                // Tampilkan chart setelah delay
                setTimeout(() => {
                    $('#loading-customer-review-chart').addClass('d-none');
                    $('#customerReviewChart').removeClass('d-none');
                }, 100);
            });
        </script>

        <script>
            let financeChart;
            const select = $('#year');

            // Sembunyikan chart dan tampilkan loader saat awal
            $('#financeChart').addClass('d-none');
            $('#loading-finance-chart').removeClass('d-none');

            select.append('<option value="" disabled selected>Memuat tahun...</option>');
            select.select2();

            // Fungsi loadChart yang mengembalikan Promise
            function loadChart(year) {
                $('#loading-finance-chart').removeClass('d-none');
                $('#financeChart').addClass('d-none');

                return fetch("{{ route('chart.finance') }}?year=" + year)
                    .then(response => response.json())
                    .then(data => {
                        const ctx = document.getElementById("financeChart").getContext('2d');
                        if (financeChart) financeChart.destroy();

                        financeChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                        label: data.datasets[0].label,
                                        data: data.datasets[0].data,
                                        backgroundColor: 'rgba(32, 201, 151, 1)',
                                    },
                                    {
                                        label: data.datasets[1].label,
                                        data: data.datasets[1].data,
                                        backgroundColor: 'rgba(220, 53, 69, 1)',
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                legend: {
                                    display: true
                                },
                                tooltips: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            const value = tooltipItem.yLabel || tooltipItem.value;
                                            return 'Rp ' + Number(value).toLocaleString('id-ID');
                                        }
                                    }
                                },
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero: true,
                                            callback: value => 'Rp ' + value.toLocaleString('id-ID')
                                        },
                                        gridLines: {
                                            color: '#f2f2f2',
                                            drawBorder: false
                                        }
                                    }],
                                    xAxes: [{
                                        gridLines: {
                                            display: false
                                        }
                                    }]
                                }
                            }
                        });

                        // Tampilkan chart setelah sukses render
                        $('#loading-finance-chart').addClass('d-none');
                        $('#financeChart').removeClass('d-none');

                        const totalRevenue = Number(data.total_revenue) || 0;
                        const totalExpense = Number(data.total_expense) || 0;

                        $('#total_revenue_by_year').text('Rp ' + totalRevenue.toLocaleString('id-ID'));
                        $('#total_expense_by_year').text('Rp ' + totalExpense.toLocaleString('id-ID'));
                    })
                    .catch(err => {
                        console.error('Gagal memuat data:', err);
                        $('#loading-finance-chart').addClass('d-none');
                        $('#financeChart').addClass('d-none');
                    });
            }

            // Ambil tahun dan isi select
            fetch("{{ route('chart.finance.years') }}")
                .then(res => res.json())
                .then(years => {
                    const currentYear = new Date().getFullYear();

                    select.empty();

                    const uniqueYears = new Set(years);
                    uniqueYears.add(currentYear); // Tambahkan tahun sekarang jika belum ada

                    Array.from(uniqueYears).sort().reverse().forEach(year => {
                        const isSelected = (year == currentYear) ? 'selected' : '';
                        select.append(`<option value="${year}" ${isSelected}>${year}</option>`);
                    });

                    select.trigger('change.select2');

                    // Load chart berdasarkan tahun saat ini
                    return loadChart(currentYear);
                })
                .catch(err => {
                    console.error('Gagal memuat daftar tahun:', err);
                    $('#loading-finance-chart').addClass('d-none');
                });

            // Event saat tahun dipilih
            $('#year').on('change', function() {
                const selectedYear = $(this).val();
                loadChart(selectedYear);
            });
        </script>
    @else
        <script>
            Promise.all([
                fetch("{{ route('statistic.order') }}").then(res => res.json()),
                fetch("{{ route('statistic.payment') }}").then(res => res.json()),
            ]).then(([orderData, paymentData]) => {
                // Order
                document.getElementById('order-done-count').textContent = orderData.count_by_status.done;
                document.getElementById('order-canceled-count').textContent = orderData.count_by_status.canceled;
                document.getElementById('order-total-count').textContent = orderData.count_by_status.total;

                // Payment
                document.getElementById('payment-unpaid-count').textContent = paymentData.count_by_status.unpaid;
                document.getElementById('payment-partial-count').textContent = paymentData.count_by_status.partial;
                document.getElementById('payment-paid-count').textContent = paymentData.count_by_status.paid;
                document.getElementById('payment-total-count').textContent = paymentData.count_by_status.total;
            });
        </script>
    @endif

    <!-- Date and time -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const timeEl = document.getElementById("server-time");
            const serverTimestamp = parseInt(timeEl.getAttribute("data-servertime"));

            // Hitung selisih waktu antara server dan client
            const clientTimestamp = new Date().getTime();
            const timeDiff = serverTimestamp - clientTimestamp;

            function updateTime() {
                // Ambil waktu sekarang + selisih dari server
                const now = new Date(new Date().getTime() + timeDiff);

                const days = [
                    'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
                ];
                const months = [
                    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];

                const day = days[now.getDay()];
                const date = now.getDate();
                const month = months[now.getMonth()];
                const year = now.getFullYear();

                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');

                const formatted = `${day}, ${date} ${month} ${year} | ${hours}:${minutes}:${seconds}`;
                timeEl.textContent = formatted + " {{ $zone }}";
            }

            // Update setiap detik
            updateTime();
            setInterval(updateTime, 1000);
        });
    </script>
@endpush

<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerReview;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Staff;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    protected function clearDashboardCache()
    {
        $now = Carbon::now();
        $monthKey = $now->format('Y_m');
        $yearKey = $now->format('Y');

        // Daftar key cache yang akan dihapus
        $keys = [
            "order_statistic_{$monthKey}",
            "payment_statistic_{$monthKey}",
            "revenue_chart_{$monthKey}",
            "expense_chart_{$monthKey}",
            "finance_chart_{$yearKey}",
            'customer_review_chart',
            'staff_statistic',
            'customer_statistic',
        ];

        // Hapus semua key dari cache
        foreach ($keys as $key) {
            if (Cache::has($key)) {
                Cache::forget($key);
            } else {
                Log::info("Cache key '{$key}' tidak ditemukan saat percobaan penghapusan oleh user ID: " . Auth::user()->id);
            }
        }
    }

    public function callToClearDashboardCache()
    {
        $this->clearDashboardCache();

        return redirect(url('/dashboard'));
    }

    public function index()
    {
        $user = Auth::user();

        // Jika user adalah customer, tampilkan data kosong
        if ($user && $user->role === 'customer') {
            $lastActivityUsers = [];
        } else {
            // Ambil semua sesi user yang aktif
            $sessions = DB::table('sessions')
                ->orderBy('last_activity', 'desc')
                ->get()
                ->groupBy('user_id');

            $userIds = $sessions->keys();

            // Ambil user terkait berdasarkan role yang sesuai
            $usersQuery = User::with(['relatedCustomer', 'relatedStaff'])
                ->whereIn('id', $userIds);


            // Jika admin atau karyawan, tidak boleh lihat aktivitas owner
            if ($user && in_array($user->role, ['admin', 'employee'])) {
                $usersQuery->where('role', '!=', 'owner');
            }

            $users = $usersQuery->get();

            $lastActivityUsers = $users->map(function ($userItem) use ($sessions) {
                $fullname = $userItem->relatedCustomer?->fullname
                    ?? $userItem->relatedStaff?->fullname
                    ?? 'N/A';

                $lastActivity = optional($sessions[$userItem->id]->first())->last_activity;

                return (object)[
                    'fullname' => $fullname,
                    'role' => $userItem->role,
                    'last' => $lastActivity ? $lastActivity : null, // simpan sebagai timestamp untuk sorting
                ];
            })
                ->filter(fn($item) => $item->last !== null) // hanya yang punya aktivitas
                ->sortByDesc('last')                        // urutkan dari yang terbaru
                ->take(3)                                   // ambil 3 teratas
                ->map(function ($item) {
                    // konversi timestamp jadi string tanggal jika ingin ditampilkan
                    $item->last = date('Y-m-d H:i:s', $item->last);
                    return $item;
                })
                ->values(); // reset indeks
        }

        return view('dashboard.index', ['lastActivityUsers' => $lastActivityUsers]);
    }

    public function orderStatistic()
    {
        $user = Auth::user();
        $isCustomer = $user->relatedCustomer && $user->role === 'customer';
        $customerId = $user->relatedCustomer->id ?? null;

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $cacheKey = 'order_statistic_' . $now->format('Y_m');

        // Ambil semua order yang diperlukan (canceled & done) dalam periode bulan ini
        $orders = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($startOfMonth, $endOfMonth) {
            return Order::select('order_status', 'customer_id')
                ->whereIn('order_status', ['canceled', 'done'])
                ->whereNotNull('order_code')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->get();
        });

        // Setelah data diambil (dari cache atau DB), filter berdasarkan customer jika perlu
        if ($isCustomer) {
            $orders = $orders->where('customer_id', $customerId);
        }

        // Hitung jumlah status
        $canceled = $orders->where('order_status', 'canceled')->count();
        $done = $orders->where('order_status', 'done')->count();

        $result = [
            'count_by_status' => [
                'canceled' => $canceled,
                'done' => $done,
                'total' => $canceled + $done,
            ]
        ];

        return response()->json($result);
    }

    public function paymentStatistic()
    {
        $user = Auth::user();
        $isCustomer = $user->relatedCustomer && $user->role === 'customer';
        $customerId = $user->relatedCustomer->id ?? null;

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $cacheKey = 'payment_statistic_' . $now->format('Y_m');

        // Ambil semua order dengan status pembayaran yang diperlukan (unpaid, partial & paid) dalam periode bulan ini
        $payments = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($startOfMonth, $endOfMonth) {
            return Order::select('payment_status', 'customer_id')
                ->whereIn('payment_status', ['unpaid', 'partial', 'paid'])
                ->whereNotNull('order_code')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->get();
        });

        // Setelah data diambil (dari cache atau DB), filter berdasarkan customer jika perlu
        if ($isCustomer) {
            $payments = $payments->where('customer_id', $customerId);
        }

        // Hitung jumlah status
        $unpaid = $payments->where('payment_status', 'unpaid')->count();
        $partial = $payments->where('payment_status', 'partial')->count();
        $paid = $payments->where('payment_status', 'paid')->count();

        $result = [
            'count_by_status' => [
                'unpaid' => $unpaid,
                'partial' => $partial,
                'paid' => $paid,
                'total' => $unpaid + $partial + $paid,
            ]
        ];

        return response()->json($result);
    }

    public function customerStatistic()
    {
        $cacheKey = 'customer_statistic';

        $result = Cache::remember($cacheKey, now()->addMinutes(60), function () {
            // Hitung jumlah berdasarkan customer_type secara langsung dari database
            $memberCount = Customer::where('customer_type', 'member')->count();
            $nonMemberCount = Customer::where('customer_type', 'non_member')->count();

            return [
                'count_by_customer_type' => [
                    'member' => $memberCount,
                    'non_member' => $nonMemberCount,
                    'total' => $memberCount + $nonMemberCount,
                ]
            ];
        });

        return response()->json($result);
    }

    public function staffStatistic()
    {
        $cacheKey = 'staff_statistic';

        $result = Cache::remember($cacheKey, now()->addMinutes(60), function () {
            $ownerCount = Staff::where('position', 'owner')->count();
            $adminCount = Staff::where('position', 'admin')->count();
            $employeeCount = Staff::where('position', 'employee')->count();

            return [
                'count_by_staff_position' => [
                    'owner' => $ownerCount,
                    'admin' => $adminCount,
                    'employee' => $employeeCount,
                    'total' => $ownerCount + $adminCount + $employeeCount,
                ]
            ];
        });

        return response()->json($result);
    }

    public function revenueChart()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Gunakan cache dengan key unik
        $cacheKey = 'revenue_chart_' . $now->format('Y_m');

        $result = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($startOfMonth, $endOfMonth) {
            $transactions = Transaction::where('status', 'success')
                ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
                ->get();

            $grouped = $transactions->groupBy(function ($item) {
                return Carbon::parse($item->paid_at)->format('d-m-Y');
            })->map(function ($group, $date) {
                return [
                    'label' => carbon_format_date($date, 'date'),
                    'amount' => $group->sum('amount_paid'),
                ];
            })->values();

            $totalRevenue = $transactions->sum('amount_paid');

            return [
                'data' => $grouped,
                'total_revenue_this_month' => $totalRevenue,
            ];
        });

        return response()->json($result);
    }

    public function expenseChart()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Gunakan cache dengan key unik
        $cacheKey = 'expense_chart_' . $now->format('Y_m');

        $result = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($startOfMonth, $endOfMonth) {
            $expenses = Expense::whereIn('status', ['partial', 'paid'])
                ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
                ->get();

            $grouped = $expenses->groupBy(function ($item) {
                return Carbon::parse($item->paid_at)->format('d-m-Y');
            })->map(function ($group, $date) {
                return [
                    'label' => carbon_format_date($date, 'date'),
                    'amount' => $group->sum('paid_amount'),
                ];
            })->values();

            $totalExpense = $expenses->sum('paid_amount');

            return [
                'data' => $grouped,
                'total_expense_this_month' => $totalExpense,
            ];
        });

        return response()->json($result);
    }

    public function getAvailableFinanceYears()
    {
        $transactionYears = Transaction::selectRaw('YEAR(paid_at) as year')
            ->whereNotNull('paid_at')
            ->distinct()
            ->pluck('year')
            ->toArray();

        $expenseYears = Expense::selectRaw('YEAR(paid_at) as year')
            ->whereNotNull('paid_at')
            ->distinct()
            ->pluck('year')
            ->toArray();

        // Gabungkan dan filter null & duplikat
        $allYears = array_filter(array_unique(array_merge($transactionYears, $expenseYears)));

        rsort($allYears); // Urut dari terbaru

        // Jika kosong, ambil tahun saat ini
        if (empty($allYears)) {
            $allYears[] = date('Y');
        }

        return response()->json($allYears);
    }

    public function financeChart(Request $request)
    {
        $year = $request->input('year', Carbon::now()->format('Y'));
        $cacheKey = 'finance_chart_' . $year;

        $result = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($year) {
            $monthlyRevenue = array_fill(0, 12, 0);
            $monthlyExpense = array_fill(0, 12, 0);

            $transactions = Transaction::where('status', 'success')
                ->whereYear('paid_at', $year)
                ->get();

            foreach ($transactions as $transaction) {
                $monthIndex = Carbon::parse($transaction->paid_at)->month - 1;
                $monthlyRevenue[$monthIndex] += $transaction->amount_paid;
            }

            $expenses = Expense::whereIn('status', ['partial', 'paid'])
                ->whereYear('paid_at', $year)
                ->get();

            foreach ($expenses as $expense) {
                $monthIndex = Carbon::parse($expense->paid_at)->month - 1;
                $monthlyExpense[$monthIndex] += $expense->paid_amount;
            }

            return [
                'labels' => [
                    "Januari",
                    "Februari",
                    "Maret",
                    "April",
                    "Mei",
                    "Juni",
                    "Juli",
                    "Agustus",
                    "September",
                    "Oktober",
                    "November",
                    "Desember"
                ],
                'datasets' => [
                    [
                        'label' => 'Pendapatan',
                        'data' => $monthlyRevenue,
                    ],
                    [
                        'label' => 'Pengeluaran',
                        'data' => $monthlyExpense,
                    ]
                ],
                'total_revenue' => array_sum($monthlyRevenue),
                'total_expense' => array_sum($monthlyExpense),
            ];
        });

        return response()->json($result);
    }

    public function customerReviewChart()
    {
        $cacheKey = 'customer_review_chart';

        // Cek cache selama 60 menit
        $cachedData = Cache::remember($cacheKey, 60, function () {
            // Ambil jumlah masing-masing rating dari 1-5
            $ratings = CustomerReview::select('rating', DB::raw('count(*) as total'))
                ->groupBy('rating')
                ->pluck('total', 'rating')
                ->toArray();

            // Pastikan semua rating 1-5 tetap ada
            $completeRatings = [];
            for ($i = 1; $i <= 5; $i++) {
                $completeRatings[$i] = $ratings[$i] ?? 0;
            }

            return [
                'ratings' => $completeRatings,
            ];
        });

        return response()->json($cachedData);
    }

    public function notification()
    {
        $user = Auth::user();
        $isEmployee = $user->relatedStaff && $user->role === 'employee';

        // --- Pesanan Baru ---
        $orderQuery = Order::with('orderingCustomer')
            ->where('order_status', 'new')
            ->whereNotNull('order_code')
            ->whereNull('staff_id')
            ->latest('created_at');

        $orderCount = $orderQuery->count();
        $allOrders = $orderQuery->get();

        $orders = $allOrders->map(function ($order) {
            $createdAt = Carbon::parse($order->created_at);
            return [
                'id' => $order->id,
                'name' => $order->orderingCustomer->fullname ?? 'N/A',
                'time' => $createdAt->locale('id')->diffForHumans(),
                'timestamp' => $createdAt->timestamp,
                'type' => 'order',
                'url' => url("/order/{$order->id}/edit"),
            ];
        });

        // --- Ulasan Baru ---
        $reviewQuery = CustomerReview::with('reviewingCustomer')
            ->where('is_read', false)
            ->whereNotNull('customer_id')
            ->latest('review_at');

        $reviewCount = $reviewQuery->count();
        $allReviews = $reviewQuery->get();

        $reviews = $allReviews->map(function ($review) {
            $createdAt = Carbon::parse($review->created_at);
            return [
                'id' => $review->id,
                'name' => $review->reviewingCustomer->fullname ?? 'N/A',
                'time' => $createdAt->locale('id')->diffForHumans(),
                'timestamp' => $createdAt->timestamp,
                'type' => 'review',
                'url' => url("/customer-review?is_read=0&rating=all"),
            ];
        });

        // --- Transaksi Pending ---
        $transactionQuery = Transaction::with(['relatedOrder.orderingCustomer'])
            ->where('status', 'pending')
            ->whereNotNull('order_id')
            ->latest('paid_at');

        $transactionCount = $transactionQuery->count();
        $allTransactions = $transactionQuery->get();

        $transactions = $allTransactions->map(function ($trx) {
            $paidAt = Carbon::parse($trx->paid_at);
            return [
                'id' => $trx->id,
                'name' => $trx->relatedOrder->orderingCustomer->fullname ?? 'N/A',
                'time' => $paidAt->locale('id')->diffForHumans(),
                'timestamp' => $paidAt->timestamp,
                'type' => 'transaction',
                'url' => url("/order/{$trx->relatedOrder->id}/transaction/{$trx->id}/edit"),
            ];
        });

        // Gabungkan dan sort data
        if ($isEmployee) {
            $merged = $orders->sortByDesc('timestamp')->values();

            return view('dashboard.notification.index', [
                'orderCount' => $orderCount,
                'orders' => $orders,
                'all' => $merged,
            ]);
        }

        $merged = collect()
            ->merge($orders)
            ->merge($reviews)
            ->merge($transactions)
            ->sortByDesc('timestamp')
            ->values();

        return view('dashboard.notification.index', [
            'orderCount' => $orderCount,
            'reviewCount' => $reviewCount,
            'transactionCount' => $transactionCount,
            'orders' => $orders,
            'reviews' => $reviews,
            'transactions' => $transactions,
            'all' => $merged,
        ]);
    }

    public function getNotifications()
    {
        $user = Auth::user();
        $isEmployee = $user->relatedStaff && $user->role === 'employee';

        // --- Pesanan Baru ---
        $orderQuery = Order::where('order_status', 'new')
            ->whereNotNull('order_code')
            ->whereNull('staff_id')
            ->orderBy('created_at', 'desc');

        $orderCount = $orderQuery->count();

        // --- Ulasan Baru ---
        $reviewQuery = CustomerReview::where('is_read', false)
            ->whereNotNull('customer_id')
            ->orderBy('review_at', 'desc');

        $reviewCount = $reviewQuery->count();

        // --- Transaksi Pending ---
        $transactionQuery = Transaction::where('status', 'pending')
            ->whereNotNull('order_id')
            ->orderBy('paid_at', 'desc');

        $transactionCount = $transactionQuery->count();

        return response()->json([
            'count' => $isEmployee ? $orderCount : $orderCount + $reviewCount + $transactionCount,
        ]);
    }
}

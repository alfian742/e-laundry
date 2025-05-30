<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    // Fungsi untuk menghapus cache pengeluaran berdasarkan kombinasi tahun & bulan
    protected function clearExpenseCache()
    {
        $yearMonthPairs = Expense::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
            ->distinct()
            ->get();

        foreach ($yearMonthPairs as $pair) {
            $year = $pair->year;
            $monthFormatted = str_pad($pair->month, 2, '0', STR_PAD_LEFT); // Format bulan dua digit
            $cacheKey = "expense_cache_key_{$year}_{$monthFormatted}";
            Cache::forget($cacheKey);
        }
    }

    // Menampilkan semua data pengeluaran
    public function index(Request $request)
    {
        // Ambil semua tahun unik dari pengeluaran
        $availableYears = Expense::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        // Ambil tahun dan bulan sekarang
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // Ambil input dari query string
        $requestedYear = $request->input('year');
        $requestedMonth = $request->input('month');

        // Tentukan tahun yang akan digunakan:
        // Jika tidak ada input, pakai tahun sekarang jika tersedia, jika tidak pakai tahun terbaru
        $selectedYear = $requestedYear ?? ($availableYears->contains($currentYear) ? $currentYear : $availableYears->first());

        // Ambil bulan unik berdasarkan tahun yang dipilih
        $availableMonths = Expense::selectRaw('MONTH(created_at) as month')
            ->whereYear('created_at', $selectedYear)
            ->distinct()
            ->orderByDesc('month')
            ->pluck('month')
            ->map(function ($month) {
                return str_pad($month, 2, '0', STR_PAD_LEFT); // Format dua digit
            });

        // Tentukan bulan yang akan digunakan
        if ($requestedMonth && $availableMonths->contains($requestedMonth)) {
            $selectedMonth = $requestedMonth;
        } elseif ($availableMonths->contains(str_pad($currentMonth, 2, '0', STR_PAD_LEFT))) {
            $selectedMonth = str_pad($currentMonth, 2, '0', STR_PAD_LEFT);
        } else {
            $selectedMonth = $availableMonths->first(); // fallback ke bulan terbaru dari DB
        }

        // Cache key berdasarkan tahun dan bulan
        $cacheKey = "expense_cache_key_{$selectedYear}_{$selectedMonth}";

        $monthLabels = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        // Ambil data pengeluaran dari cache atau database
        $expenses = Cache::remember($cacheKey, 300, function () use ($selectedYear, $selectedMonth) {
            return Expense::whereYear('created_at', $selectedYear)
                ->whereMonth('created_at', $selectedMonth)
                ->orderByDesc('created_at')
                ->get();
        });

        // Kirim data ke view
        return view('dashboard.finance.expense.index', compact(
            'expenses',
            'monthLabels',
            'availableYears',
            'availableMonths',
            'selectedYear',
            'selectedMonth'
        ));
    }

    public function create()
    {
        return view('dashboard.finance.expense.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'expense_category' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value > $request->input('total_amount')) {
                        $fail('Jumlah pembayaran tidak boleh lebih besar dari total tagihan.');
                    }
                }
            ],
            'notes' => 'nullable|string',
            'status' => 'required|in:unpaid,partial,paid',
            'paid_at' => [
                $request->input('status') !== 'unpaid' ? 'required' : 'nullable',
                'date'
            ],
        ];

        $messages = [
            'expense_category.required' => 'Jenis pengeluaran wajib diisi.',
            'expense_category.string' => 'Jenis pengeluaran harus berupa teks.',
            'expense_category.max' => 'Jenis pengeluaran maksimal 255 karakter.',

            'total_amount.required' => 'Total tagihan wajib diisi.',
            'total_amount.numeric' => 'Total tagihan harus berupa angka.',
            'total_amount.min' => 'Total tagihan tidak boleh kurang dari 0.',

            'paid_amount.required' => 'Jumlah pembayaran wajib diisi.',
            'paid_amount.numeric' => 'Jumlah pembayaran harus berupa angka.',
            'paid_amount.min' => 'Jumlah pembayaran tidak boleh kurang dari 0.',

            'notes.string' => 'Keterangan harus berupa teks.',

            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',

            'paid_at.required' => 'Tanggal pembayaran wajib diisi.',
            'paid_at.date' => 'Tanggal pembayaran tidak valid.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            Expense::create([
                'expense_category' => $data['expense_category'],
                'status'           => $data['status'] ?? 'unpaid',
                'total_amount'     => $data['total_amount'] ?? 0,
                'paid_amount'      => $data['status'] !== 'unpaid' ? $data['paid_amount'] : 0,
                'notes'            => $data['notes'] ?? null,
                'paid_at'          => $data['status'] !== 'unpaid' ? $data['paid_at'] : null,
            ]);

            $this->clearExpenseCache();

            return redirect(url('/expense'))->with('success', "Data berhasil ditambahkan.");
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Data gagal ditambahkan. Silakan coba kembali.');
        }
    }

    public function edit($id)
    {
        $expense = Expense::findOrFail($id);

        return view('dashboard.finance.expense.edit', compact('expense'));
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $rules = [
            'expense_category' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value > $request->input('total_amount')) {
                        $fail('Jumlah pembayaran tidak boleh lebih besar dari total tagihan.');
                    }
                }
            ],
            'notes' => 'nullable|string',
            'status' => 'required|in:unpaid,partial,paid',
            'paid_at' => [
                $request->input('status') !== 'unpaid' ? 'required' : 'nullable',
                'date'
            ],
        ];

        $messages = [
            'expense_category.required' => 'Jenis pengeluaran wajib diisi.',
            'expense_category.string' => 'Jenis pengeluaran harus berupa teks.',
            'expense_category.max' => 'Jenis pengeluaran maksimal 255 karakter.',

            'total_amount.required' => 'Total tagihan wajib diisi.',
            'total_amount.numeric' => 'Total tagihan harus berupa angka.',
            'total_amount.min' => 'Total tagihan tidak boleh kurang dari 0.',

            'paid_amount.required' => 'Jumlah pembayaran wajib diisi.',
            'paid_amount.numeric' => 'Jumlah pembayaran harus berupa angka.',
            'paid_amount.min' => 'Jumlah pembayaran tidak boleh kurang dari 0.',

            'notes.string' => 'Keterangan harus berupa teks.',

            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',

            'paid_at.required' => 'Tanggal pembayaran wajib diisi.',
            'paid_at.date' => 'Tanggal pembayaran tidak valid.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            $expense->update([
                'expense_category' => $data['expense_category'],
                'status'           => $data['status'] ?? 'unpaid',
                'total_amount'     => $data['total_amount'] ?? 0,
                'paid_amount'      => $data['status'] !== 'unpaid' ? $data['paid_amount'] : 0,
                'notes'            => $data['notes'] ?? null,
                'paid_at'          => $data['status'] !== 'unpaid' ? $data['paid_at'] : null,
            ]);

            $this->clearExpenseCache();

            return redirect(url('/expense'))->with('success', "Data berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Data gagal diperbarui. Silakan coba kembali.');
        }
    }

    // Menghapus data pengeluaran
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);

        try {
            $expense->forceDelete(); // Karena menggunakan soft delete

            $this->clearExpenseCache();

            return redirect(url('/expense'))->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Data gagal dihapus. Silakan coba kembali.');
        }
    }

    // Ambil bulan yang tersedia dan kirim ke AJAX
    public function getAvailableMonths(Request $request)
    {
        $year = $request->query('year');

        if (!$year) {
            return response()->json([]);
        }

        $months = Expense::selectRaw('MONTH(created_at) as month')
            ->whereYear('created_at', $year)
            ->distinct()
            ->orderBy('month')
            ->pluck('month')
            ->map(function ($month) {
                $formatted = str_pad($month, 2, '0', STR_PAD_LEFT);
                $labels = [
                    '01' => 'Januari',
                    '02' => 'Februari',
                    '03' => 'Maret',
                    '04' => 'April',
                    '05' => 'Mei',
                    '06' => 'Juni',
                    '07' => 'Juli',
                    '08' => 'Agustus',
                    '09' => 'September',
                    '10' => 'Oktober',
                    '11' => 'November',
                    '12' => 'Desember',
                ];
                return [
                    'id' => $formatted,
                    'text' => $labels[$formatted] ?? $formatted,
                ];
            });

        return response()->json($months);
    }

    public function printReport(Request $request)
    {
        // Validasi input
        $rules = [
            'early_period' => 'required|date',
            'final_period' => 'required|date|after_or_equal:early_period',
        ];

        $messages = [
            'early_period.required' => 'Periode awal wajib diisi.',
            'final_period.required' => 'Periode akhir wajib diisi.',
            'final_period.after_or_equal' => 'Periode akhir harus sama atau setelah periode awal.',
        ];

        $data = $request->validate($rules, $messages);

        // Pastikan mencakup hari penuh
        $early_period = Carbon::parse($data['early_period']);
        $final_period = Carbon::parse($data['final_period']);

        // Ambil data pengeluaran berdasarkan periode waktu pembayaran
        $expenses = Expense::whereBetween('paid_at', [$early_period, $final_period])
            ->whereIn('status', ['partial', 'paid'])
            ->orderBy('paid_at', 'asc')
            ->get();

        // Hapus cache (jika diperlukan)
        $this->clearExpenseCache();

        return view('dashboard.finance.expense.report', compact('expenses', 'early_period', 'final_period'));
    }
}

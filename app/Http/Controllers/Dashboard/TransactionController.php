<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ProofOfPayment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    // Fungsi untuk menghapus cache transaksi berdasarkan kombinasi tahun & bulan
    protected function clearTransactionCache()
    {
        $yearMonthPairs = Transaction::selectRaw('YEAR(paid_at) as year, MONTH(paid_at) as month')
            ->distinct()
            ->get();

        foreach ($yearMonthPairs as $pair) {
            $year = $pair->year;
            $monthFormatted = str_pad($pair->month, 2, '0', STR_PAD_LEFT); // Format bulan dua digit
            $cacheKey = "transaction_cache_key_{$year}_{$monthFormatted}";
            Cache::forget($cacheKey);
        }
    }

    // Menampilkan semua transaski dengan status sukses
    public function index(Request $request)
    {
        // Ambil semua tahun unik dari transaksi
        $availableYears = Transaction::selectRaw('YEAR(paid_at) as year')
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
        $availableMonths = Transaction::selectRaw('MONTH(paid_at) as month')
            ->whereYear('paid_at', $selectedYear)
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
        $cacheKey = "transaction_cache_key_{$selectedYear}_{$selectedMonth}";

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

        // Ambil data transaksi dari cache atau database
        $transactions = Cache::remember($cacheKey, 300, function () use ($selectedYear, $selectedMonth) {
            return Transaction::whereYear('paid_at', $selectedYear)
                ->whereMonth('paid_at', $selectedMonth)
                ->where('status', 'success')
                ->orderByDesc('paid_at')
                ->get();
        });

        // Kirim data ke view
        return view('dashboard.finance.revenue.index', compact(
            'transactions',
            'monthLabels',
            'availableYears',
            'availableMonths',
            'selectedYear',
            'selectedMonth'
        ));
    }

    // Ambil bulan yang tersedia dan kirim ke AJAX
    public function getAvailableMonths(Request $request)
    {
        $year = $request->query('year');

        if (!$year) {
            return response()->json([]);
        }

        $months = Transaction::selectRaw('MONTH(paid_at) as month')
            ->whereYear('paid_at', $year)
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
        $early_period = Carbon::parse($data['early_period'])->startOfDay();
        $final_period = Carbon::parse($data['final_period'])->endOfDay();

        // Ambil data transaksi berdasarkan periode
        $transactions = Transaction::whereBetween('paid_at', [$early_period, $final_period])
            ->where('status', 'success')
            ->orderBy('paid_at', 'asc')
            ->get();

        // Hapus cache (jika diperlukan)
        $this->clearTransactionCache();

        return view('dashboard.finance.revenue.report', compact('transactions', 'early_period', 'final_period'));
    }

    public function indexTransactionOrder($orderId)
    {
        $order = Order::with(['orderingCustomer', 'orderDetails', 'orderTransactions'])->findOrFail($orderId);

        $user = Auth::user();
        $customer = $user->relatedCustomer;

        // Jika status pesanan baru/dibatalkan atau status pembayaran lunas, redirect ke detail pesanan
        if ($customer && (in_array($order->order_status, ['new', 'canceled']) || $order->payment_status === 'paid')) {
            return redirect(url("/order/{$order->id}"));
        }

        $customer = $order->orderingCustomer;
        $details = $order->orderDetails;
        $transactions = $order->orderTransactions()->orderBy('paid_at', 'desc')->get();

        return view('dashboard.order.manage-transaction.index', compact('order', 'customer', 'details', 'transactions'));
    }

    public function createTransactionOrder($orderId)
    {
        $order = Order::with(['orderDetails', 'orderTransactions'])->findOrFail($orderId);

        $user = Auth::user();
        $customer = $user->relatedCustomer;

        // Customer tidak boleh membuat transaksi ke pesanan yang bukan miliknya
        if ($customer && $order->customer_id !== $customer->id) {
            return back()->with('warning', 'Pesanan tidak ditemukan.');
        }

        // Customer tidak boleh membuat transaksi jika pesanan baru/dibatalkan
        if ($customer && in_array($order->order_status, ['new', 'canceled'])) {
            return back()->with('warning', 'Pembayaran tidak dapat dilakukan.');
        }

        // Customer tidak boleh membuat transaksi jika pembayaran lunas
        if ($customer && $order->payment_status === 'paid') {
            return back()->with('warning', 'Pesanan sudah dibayar lunas.');
        }

        $details = $order->orderDetails;
        $transactions = $order->orderTransactions;

        $paymentMethodcacheKey = 'payment_method_cache_key'; // Pastikan sama dengan cache key pada PaymentMethodController
        $paymentMethods = Cache::remember($paymentMethodcacheKey, 300, function () {
            return PaymentMethod::orderBy('created_at', 'desc')->get();
        })->where('active', true)->sortBy('method_name');

        return view('dashboard.order.manage-transaction.create', compact('order', 'details', 'transactions', 'paymentMethods'));
    }

    public function storeTransactionOrder(Request $request, $orderId)
    {
        $order = Order::with(['orderDetails', 'orderTransactions'])->findOrFail($orderId);

        $user = Auth::user();
        $customer = $user->relatedCustomer;
        $staff = $user->relatedStaff;

        // Customer tidak boleh membuat transaksi ke pesanan yang bukan miliknya
        if ($customer && $order->customer_id !== $customer->id) {
            return back()->with('warning', 'Pesanan tidak ditemukan.');
        }

        // Customer tidak boleh membuat transaksi jika pesanan baru/dibatalkan
        if ($customer && in_array($order->order_status, ['new', 'canceled'])) {
            return back()->with('warning', 'Pembayaran tidak dapat dilakukan.');
        }

        // Customer tidak boleh membuat transaksi jika pembayaran lunas
        if ($customer && $order->payment_status === 'paid') {
            return back()->with('warning', 'Pesanan sudah dibayar lunas.');
        }

        // Validasi dasar
        $rules = [
            'payment_method_id' => 'required|exists:payment_methods,id',
        ];

        // Jika customer
        if ($customer) {
            $rules['img'] = ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'];

            if ($request->filled('payment_method_id')) {
                $paymentMethod = PaymentMethod::find($request->input('payment_method_id'));

                if ($paymentMethod && in_array($paymentMethod->payment_type, ['online', 'bank_transfer'])) {
                    $rules['img'][0] = 'required'; // ubah nullable jadi required
                }
            }
        }

        // Jika staff
        if ($staff) {
            $rules = array_merge($rules, [
                'amount_paid' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:255',
                'status' => 'required|in:pending,success,rejected,failed',
            ]);
        }

        $messages = [
            'payment_method_id.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method_id.exists' => 'Metode pembayaran yang dipilih tidak valid.',

            'amount_paid.required' => 'Jumlah pembayaran wajib diisi.',
            'amount_paid.numeric' => 'Jumlah pembayaran harus berupa angka.',
            'amount_paid.min' => 'Jumlah pembayaran tidak boleh kurang dari 0.',

            'notes.string' => 'Keterangan harus berupa teks.',
            'notes.max' => 'Keterangan tidak boleh lebih dari 255 karakter.',

            'status.required' => 'Status pembayaran wajib dipilih.',
            'status.in' => 'Status pembayaran tidak valid.',

            'img.required' => 'Bukti pembayaran wajib diunggah.',
            'img.image' => 'File yang diunggah harus berupa gambar.',
            'img.mimes' => 'Format gambar harus PNG, JPG, JPEG, atau WEBP.',
            'img.max' => 'Ukuran gambar maksimal 2MB.',
        ];

        $data = $request->validate($rules, $messages);

        $uploadedFilePath = null; // set default path null

        try {
            DB::beginTransaction();

            $defaultTimestamp = now();

            // Buat transaksi baru
            $transaction = Transaction::create([
                'invoice_id' => 'INV-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(5)),
                'order_id' => $order->id,
                'payment_method_id' => $data['payment_method_id'],
                'amount_paid' => $staff ? $data['amount_paid'] : 0,
                'notes' => $staff ? $data['notes'] : null,
                'status' => $staff ? $data['status'] : 'pending',
                'paid_at' => $defaultTimestamp,
            ]);

            // Buat bukti pembayaran baru jika customer unggah bukti pembayaran
            if ($customer && $request->hasFile('img')) {
                $file = $request->file('img');

                $timestamp = $defaultTimestamp->format('Ymd_His');

                $extension = $file->getClientOriginalExtension();

                $filename = 'proof_' . $timestamp . '.' . $extension;

                $uploadPath = public_path('img/uploads/proofs');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $file->move($uploadPath, $filename);

                $data['img'] = $filename;

                // Path untuk rollback jika bukti pembayaran gagal dibuat
                $uploadedFilePath = $uploadPath . '/' . $filename;

                ProofOfPayment::create([
                    'transaction_id' => $transaction->id, // Ambil id transaksi saat ini 
                    'img' => $data['img'],
                ]);
            }

            DB::commit();

            $this->clearTransactionCache();

            return redirect(url("/order/{$order->id}/transaction"))->with('success', "Pembayaran berhasil ditambahkan.");
        } catch (\Exception $e) {
            DB::rollback();

            // Hapus file jika sudah diupload tapi bukti pembayaran gagal dibuat
            if ($uploadedFilePath && file_exists($uploadedFilePath)) {
                @unlink($uploadedFilePath);
            }

            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Pembayaran gagal ditambahkan. Silakan coba kembali.');
        }
    }

    public function editTransactionOrder($orderId, $transactionId)
    {
        $order = Order::with(['orderDetails', 'orderTransactions'])->findOrFail($orderId);
        $transaction = Transaction::findOrFail($transactionId);

        $user = Auth::user();
        $customer = $user->relatedCustomer;

        // Customer tidak boleh mengedit transaksi pesanan yang bukan miliknya
        if ($customer && $order->customer_id !== $customer->id) {
            return back()->with('warning', 'Pesanan tidak ditemukan.');
        }

        // Customer tidak boleh mengedit transaksi jika pesanan baru/dibatalkan
        if ($customer && in_array($order->order_status, ['new', 'canceled'])) {
            return back()->with('warning', 'Tidak dapat mengubah pembayaran.');
        }

        // Customer tidak boleh mengedit transaksi jika pembayaran lunas
        if ($customer && $order->payment_status === 'paid') {
            return back()->with('warning', 'Pesanan sudah dibayar lunas.');
        }

        // Customer tidak boleh mengedit transaksi jika pembayaran sukses gagal
        if ($customer && in_array($transaction->status, ['success', 'failed'])) {
            return back()->with('warning', 'Tidak dapat mengubah pembayaran.');
        }

        $details = $order->orderDetails;
        $orderTransactions = $order->orderTransactions;

        $paymentMethodcacheKey = 'payment_method_cache_key'; // Pastikan sama dengan cache key pada PaymentMethodController
        $paymentMethods = Cache::remember($paymentMethodcacheKey, 300, function () {
            return PaymentMethod::orderBy('created_at', 'desc')->get();
        })->where('active', true)->sortBy('method_name');

        return view('dashboard.order.manage-transaction.edit', compact('order', 'transaction', 'details', 'orderTransactions', 'paymentMethods'));
    }

    public function updateTransactionOrder(Request $request, $orderId, $transactionId)
    {
        $order = Order::with(['orderingCustomer', 'orderDetails', 'orderTransactions'])->findOrFail($orderId);
        $transaction = Transaction::findOrFail($transactionId);

        $user = Auth::user();
        $customer = $user->relatedCustomer;
        $staff = $user->relatedStaff;

        // Customer tidak boleh mengedit transaksi pesanan yang bukan miliknya
        if ($customer && $order->customer_id !== $customer->id) {
            return back()->with('warning', 'Pesanan tidak ditemukan.');
        }

        // Customer tidak boleh mengedit transaksi jika pesanan baru/dibatalkan
        if ($customer && in_array($order->order_status, ['new', 'canceled'])) {
            return back()->with('warning', 'Tidak dapat mengubah pembayaran.');
        }

        // Customer tidak boleh mengedit transaksi jika pembayaran lunas
        if ($customer && $order->payment_status === 'paid') {
            return back()->with('warning', 'Pesanan sudah dibayar lunas.');
        }

        // Customer tidak boleh mengedit transaksi jika pembayaran sukses gagal
        if ($customer && in_array($transaction->status, ['success', 'failed'])) {
            return back()->with('warning', 'Tidak dapat mengubah pembayaran.');
        }

        // Validasi dasar
        $rules = [
            'payment_method_id' => 'required|exists:payment_methods,id',
        ];

        // Jika customer
        if ($customer) {
            $rules['img'] = ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'];

            if ($request->filled('payment_method_id')) {
                $paymentMethod = PaymentMethod::find($request->input('payment_method_id'));

                if ($paymentMethod && in_array($paymentMethod->payment_type, ['online', 'bank_transfer'])) {
                    $rules['img'][0] = 'required'; // ubah nullable jadi required
                }
            }
        }

        // Jika staff
        if ($staff) {
            $rules = array_merge($rules, [
                'amount_paid' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:255',
                'status' => 'required|in:pending,success,rejected,failed',
            ]);
        }

        $messages = [
            'payment_method_id.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method_id.exists' => 'Metode pembayaran yang dipilih tidak valid.',

            'amount_paid.required' => 'Jumlah pembayaran wajib diisi.',
            'amount_paid.numeric' => 'Jumlah pembayaran harus berupa angka.',
            'amount_paid.min' => 'Jumlah pembayaran tidak boleh kurang dari 0.',

            'notes.string' => 'Keterangan harus berupa teks.',
            'notes.max' => 'Keterangan tidak boleh lebih dari 255 karakter.',

            'status.required' => 'Status pembayaran wajib dipilih.',
            'status.in' => 'Status pembayaran tidak valid.',

            'img.required' => 'Bukti pembayaran wajib diunggah.',
            'img.image' => 'File yang diunggah harus berupa gambar.',
            'img.mimes' => 'Format gambar harus PNG, JPG, JPEG, atau WEBP.',
            'img.max' => 'Ukuran gambar maksimal 2MB.',
        ];

        $data = $request->validate($rules, $messages);

        $uploadedFilePath = null; // set default path null

        try {
            DB::beginTransaction();

            $defaultTimestamp = now();

            $oldPaymentMethod = PaymentMethod::find($transaction->payment_method_id);
            $newPaymentMethod = PaymentMethod::find($data['payment_method_id']);

            $oldType = $oldPaymentMethod->payment_type ?? null;
            $newType = $newPaymentMethod->payment_type ?? null;

            $transaction->update([
                'order_id' => $order->id,
                'payment_method_id' => $data['payment_method_id'],
                'amount_paid' => $staff ? $data['amount_paid'] : 0,
                'notes' => $staff ? $data['notes'] : null,
                'status' => $staff ? $data['status'] : 'pending',
                'paid_at' => $defaultTimestamp,
            ]);

            $proof = $transaction->relatedProof;
            $uploadPath = public_path('img/uploads/proofs');

            // 1. Jika dari metode BUTUH bukti => TIDAK BUTUH bukti
            if (($staff || $customer) && in_array($oldType, ['online', 'bank_transfer']) && !in_array($newType, ['online', 'bank_transfer'])) {
                if ($proof) {
                    $oldImgPath = $uploadPath . '/' . $proof->img;
                    if (file_exists($oldImgPath)) @unlink($oldImgPath);
                    $proof->delete();
                }
            }

            // 2. Jika dari metode TIDAK BUTUH bukti => BUTUH bukti ATAU upload baru
            if ($customer && $request->hasFile('img')) {
                $file = $request->file('img');
                $extension = $file->getClientOriginalExtension();
                $filename = 'proof_' . $defaultTimestamp->format('Ymd_His') . '.' . $extension;

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $file->move($uploadPath, $filename);
                $uploadedFilePath = $uploadPath . '/' . $filename;

                if ($proof) {
                    $oldImgPath = $uploadPath . '/' . $proof->img;
                    if (file_exists($oldImgPath)) @unlink($oldImgPath);
                    $proof->update(['img' => $filename]);
                } else {
                    ProofOfPayment::create([
                        'transaction_id' => $transaction->id,
                        'img' => $filename,
                    ]);
                }
            }

            DB::commit();

            $this->clearTransactionCache();

            if ($staff) {
                $customerData = $order?->orderingCustomer;

                $fullname = $customerData->fullname ?? 'N/A';
                $phone_number = $customerData->phone_number ? formatPhoneNumber($customerData->phone_number) : 'N/A';

                // Pesan berdasarkan status pesanan
                $statusMessages = [
                    'pending' => "Pembayaran dengan kode '{$transaction->invoice_id}' sedang dalam proses verifikasi. Mohon menunggu konfirmasi dari Admin.",
                    'success' => "Pembayaran dengan kode '{$transaction->invoice_id}' telah berhasil diverifikasi. Terima kasih atas pembayaran Anda.",
                    'rejected' => "Pembayaran dengan kode '{$transaction->invoice_id}' tidak dapat diterima. Silakan periksa kembali detail pembayaran Anda dan lakukan konfirmasi ulang."
                ];


                // Menyiapkan pesan untuk notifikasi
                $messageText = $statusMessages[$data['status']] ?? 'Pembayaran telah diperbarui.';
                $message = "Hai {$fullname}, {$messageText}";
                $messageUrl = "https://wa.me/{$phone_number}?text=" . urlencode($message);

                return redirect(url("/order/{$order->id}/transaction"))->with('success-with-url', 'Pembayaran berhasil diperbarui.')->with('url', $messageUrl);
            }

            return redirect(url("/order/{$order->id}/transaction"))->with('success', 'Pembayaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            if ($uploadedFilePath && file_exists($uploadedFilePath)) {
                @unlink($uploadedFilePath);
            }
            Log::error('Error: ' . $e->getMessage());
            return back()->with('error', 'Pembayaran gagal diperbarui. Silakan coba kembali.');
        }
    }

    public function destroyTransactionOrder($orderId, $transactionId)
    {
        $order = Order::with(['orderDetails', 'orderTransactions'])->findOrFail($orderId);
        $transaction = Transaction::findOrFail($transactionId);

        if ($order->id !== $transaction->order_id) {
            return back()->with('warning', 'Pembayaran tidak ditemukan.');
        }

        if ($order->payment_status === 'paid') {
            return back()->with('warning', 'Pesanan sudah dibayar lunas.');
        }

        if (in_array($transaction->status, ['success', 'failed'])) {
            return back()->with('warning', 'Tidak dapat menghapus pembayaran.');
        }


        $uploadedFilePath = null; // set default path null

        try {
            DB::beginTransaction();

            $proof = $transaction->relatedProof;
            $uploadPath = public_path('img/uploads/proofs');

            if ($proof) {
                $oldImgPath = $uploadPath . '/' . $proof->img;
                if (file_exists($oldImgPath)) @unlink($oldImgPath);
                $proof->delete();
            }

            $transaction->forceDelete(); // karena menggunakan soft delete

            DB::commit();

            $this->clearTransactionCache();

            return redirect(url("/order/{$order->id}/transaction"))->with('success', 'Pembayaran berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            if ($uploadedFilePath && file_exists($uploadedFilePath)) {
                @unlink($uploadedFilePath);
            }
            Log::error('Error: ' . $e->getMessage());
            return back()->with('error', 'Pembayaran gagal dihapus. Silakan coba kembali.');
        }
    }
}

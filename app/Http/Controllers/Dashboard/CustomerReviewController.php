<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CustomerReview;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CustomerReviewController extends Controller
{
    // Fungsi untuk menghapus cache
    protected function clearCustomerReviewCache()
    {
        $isReads = ['all', 0, 1];      // 'all' sebagai string, bukan null
        $ratings = ['all', 1, 2, 3, 4, 5];

        foreach ($isReads as $isRead) {
            foreach ($ratings as $rating) {
                $key = "customer_review_cache_key_isread_{$isRead}_rating_{$rating}";
                Cache::forget($key);
            }
        }
    }

    // Menampilkan semua ulasan
    public function index(Request $request)
    {
        $this->isNotAllowed(['customer']);

        // Ambil semua kombinasi unik is_read dan rating dari database untuk opsi filter di tampilan
        $availableFilters = CustomerReview::select('is_read', 'rating')
            ->distinct()
            ->get();

        // Ambil input dari query string
        $selectedIsRead = $request->input('is_read', 'all');
        $selectedRating = $request->input('rating', 'all');

        // Nilai default dari form adalah 'all', konversi ke null agar tidak difilter
        $filterIsRead = ($selectedIsRead !== 'all') ? (int) $selectedIsRead : null;
        $filterRating = ($selectedRating !== 'all') ? (int) $selectedRating : null;

        // Buat cache key berdasarkan kombinasi filter
        $cacheKey = "customer_review_cache_key_isread_" . ($filterIsRead ?? 'all') . "_rating_" . ($filterRating ?? 'all');

        // Ambil data dari cache jika ada, jika tidak query ke database
        $customerReviews = Cache::remember($cacheKey, 300, function () use ($filterIsRead, $filterRating) {
            $query = CustomerReview::query();

            if (!is_null($filterIsRead)) {
                $query->where('is_read', $filterIsRead);
            }

            if (!is_null($filterRating)) {
                $query->where('rating', $filterRating);
            }

            return $query->orderByDesc('review_at')->get();
        });

        // Kirim ke view dengan nilai input agar dipertahankan
        return view('dashboard.review.index', compact(
            'customerReviews',
            'availableFilters',
            'selectedIsRead',
            'selectedRating'
        ));
    }

    // Review customer
    public function myReview()
    {
        $this->isAllowed(['customer']);

        $user = Auth::user();
        $customer = $user->relatedCustomer;

        // Ambil satu review pertama dari customer
        $review = CustomerReview::where('customer_id', $customer->id)->first();

        // Kirim ke view
        return view('dashboard.review.my-review', compact('review'));
    }

    public function store(Request $request)
    {
        $this->isAllowed(['customer']);

        $user = Auth::user();
        $customer = $user->relatedCustomer;

        $rules = [
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'required|string|max:255',
        ];

        $messages = [
            'review.required' => 'Ulasan wajib diisi.',
            'review.string' => 'Ulasan harus berupa teks.',
            'review.max' => 'Ulasan maksimal 255 karakter.',

            'rating.required' => 'Rating wajib dipilih.',
            'rating.numeric' => 'Rating harus berupa angka.',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
        ];

        $data = $request->validate($rules, $messages);


        try {
            CustomerReview::create([
                'customer_id' => $customer->id,
                'rating' => $data['rating'] ?? null,
                'review' => $data['review'] ?? null,
                'review_at' => now(),
                'is_read' => false,
            ]);

            if ($data['rating'] == 1) {
                $message = 'Maaf atas ketidaknyamanannya. Terima kasih atas ulasan Anda.';
            } elseif ($data['rating'] == 2) {
                $message = 'Terima kasih, masukan Anda sangat berarti.';
            } elseif ($data['rating'] == 3) {
                $message = 'Terima kasih, kami terus berupaya lebih baik.';
            } elseif ($data['rating'] == 4) {
                $message = 'Terima kasih, senang Anda cukup puas.';
            } elseif ($data['rating'] == 5) {
                $message = 'Terima kasih! Senang bisa memenuhi harapan Anda.';
            } else {
                $message = 'Ulasan berhasil dikirim.';
            }

            $this->clearCustomerReviewCache();

            return redirect(url('/customer-review/my-review'))->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Ulasan gagal dikirim. Silakan coba kembali.');
        }
    }

    public function update(Request $request, $id)
    {
        $this->isAllowed(['customer']);

        $user = Auth::user();
        $customer = $user->relatedCustomer;

        $customerReview = CustomerReview::findOrFail($id);

        $rules = [
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'required|string|max:255',
        ];

        $messages = [
            'review.required' => 'Ulasan wajib diisi.',
            'review.string' => 'Ulasan harus berupa teks.',
            'review.max' => 'Ulasan maksimal 255 karakter.',

            'rating.required' => 'Rating wajib dipilih.',
            'rating.numeric' => 'Rating harus berupa angka.',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            $customerReview->update([
                'customer_id' => $customer->id,
                'rating' => $data['rating'] ?? null,
                'review' => $data['review'] ?? null,
                'review_at' => now(),
                'is_read' => false,
            ]);

            if ($data['rating'] == 1) {
                $message = 'Maaf atas ketidaknyamanannya. Terima kasih atas ulasan Anda.';
            } elseif ($data['rating'] == 2) {
                $message = 'Terima kasih, masukan Anda sangat berarti.';
            } elseif ($data['rating'] == 3) {
                $message = 'Terima kasih, kami terus berupaya lebih baik.';
            } elseif ($data['rating'] == 4) {
                $message = 'Terima kasih, senang Anda cukup puas.';
            } elseif ($data['rating'] == 5) {
                $message = 'Terima kasih! Senang bisa memenuhi harapan Anda.';
            } else {
                $message = 'Ulasan berhasil diperbarui.';
            }

            $this->clearCustomerReviewCache();

            return redirect(url('/customer-review/my-review'))->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Ulasan gagal diperbarui. Silakan coba kembali.');
        }
    }

    // Menghapus data ulasan
    public function destroy($id)
    {
        $this->isNotAllowed(['customer', 'employee']);

        $customerReview = CustomerReview::findOrFail($id);

        try {
            $customerReview->delete();

            $this->clearCustomerReviewCache();

            return redirect(url('/customer-review'))->with('success', 'Ulasan berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Ulasan gagal dihapus. Silakan coba kembali.');
        }
    }

    // Tandai semua sebagai dibaca
    public function markAllAsRead()
    {
        try {
            // Ambil semua review yang belum dibaca
            CustomerReview::where('is_read', false)->update(['is_read' => true]);

            // Bersihkan cache jika ada metode ini
            $this->clearCustomerReviewCache();

            return redirect(url('/customer-review'))->with('success', 'Semua ulasan berhasil ditandai sebagai sudah dibaca.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Gagal menandai ulasan. Silakan coba kembali.');
        }
    }
}

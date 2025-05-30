<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Models\OrderServiceDetail;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // Fungsi untuk menghapus cache
    // Hapus semua cache untuk order
    protected function clearAllOrderCache()
    {
        $orderStatuses = ['all', 'new', 'pending', 'in_progress', 'pickup', 'delivery', 'done', 'canceled'];
        $paymentStatuses = ['all', 'unpaid', 'partial', 'paid'];

        foreach ($orderStatuses as $orderStatus) {
            foreach ($paymentStatuses as $paymentStatus) {
                $filter = "{$orderStatus}_{$paymentStatus}";
                Cache::forget($this->getOrderCacheKey('order', $filter));
            }
        }
    }

    // Hapus semua cache untuk jadwal
    protected function clearAllScheduleCache()
    {
        $filters = ['all', 'today', 'this_week'];
        foreach ($filters as $filter) {
            Cache::forget($this->getOrderCacheKey('schedule', $filter));
        }
    }

    // Cache key
    protected function getOrderCacheKey($type = 'order', $filter = 'all')
    {
        return "order_cache_{$type}_{$filter}";
    }

    // Menampilkan seluruh order
    public function index(Request $request)
    {
        $orderStatus = $request->input('order_status', 'all'); // default: all
        $paymentStatus = $request->input('payment_status', 'all'); // default: all
        $filter = "{$orderStatus}_{$paymentStatus}";
        $cacheKey = $this->getOrderCacheKey('order', $filter);

        $orders = Cache::remember($cacheKey, 300, function () use ($orderStatus, $paymentStatus) {
            $query = Order::with(['orderingCustomer', 'deliveryOption'])
                ->whereNotNull('order_code');

            if ($orderStatus !== 'all') {
                $query->where('order_status', $orderStatus);
            }

            if ($paymentStatus !== 'all') {
                $query->where('payment_status', $paymentStatus);
            }

            return $query->orderBy('created_at', 'desc')->get();
        });

        // Jika customer (member) login, Filter berdasarkan id customer
        $user = Auth::user();
        $customerId = $user?->role === 'customer' ? $user?->relatedCustomer?->id : null;

        if ($customerId) {
            $orders = $orders->where('customer_id', $customerId);
        }

        return view('dashboard.order.manage-order.index', compact('orders', 'orderStatus', 'paymentStatus'));
    }

    // Menampilkan detail order
    public function show($id)
    {
        $order = Order::with(['orderingCustomer', 'deliveryOption', 'orderDetails'])->findOrFail($id);

        $customer = $order->orderingCustomer;
        $delivery = $order->deliveryOption;
        $details = $order->orderDetails;
        $transactions = $order->orderTransactions;

        return view('dashboard.order.manage-order.show', compact('order', 'customer', 'delivery', 'details', 'transactions'));
    }

    // Membuat order baru (Owner, Admin, Employee)
    public function create()
    {
        $this->isNotAllowed(['customer']);

        $user = Auth::user();
        $staffId = $user->relatedStaff->id;

        try {
            DB::beginTransaction();

            // Cek apakah sudah ada order 'draft' untuk staff ini (dengan lock untuk mencegah race condition)
            $order = Order::where('staff_id', $staffId)
                ->whereNull('order_code')
                ->where('order_status', 'new')
                ->lockForUpdate() // Kunci baris untuk mencegah race condition
                ->first();

            // Jika belum ada, buat order baru
            if (!$order) {
                $order = Order::create([
                    'staff_id' => $staffId,
                    'order_status' => 'new',
                    'payment_status' => 'unpaid',
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal membuat pesanan baru', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Pesanan gagal dibuat. Silakan coba kembali.');
        }

        // Ambil detail layanan dalam order yang baru dibuat/sudah ada, beserta layanan dan promo yang terkait
        $details = OrderServiceDetail::with(['parentOrder', 'includedService.promos', 'includedPromo'])
            ->where('order_id', $order->id)
            ->get();

        // Ambil semua layanan
        $serviceCacheKey = 'service_cache_key'; // Pastikan sama dengan cache key pada ServiceController
        $services = Cache::remember($serviceCacheKey, 300, function () {
            return Service::orderBy('created_at', 'desc')->get();
        })->where('active', true)->sortBy('service_name');

        // Ambil semua pelanggan
        $customerChaceKey = 'customers_by_type_member_non_member'; // Pastikan sama dengan cache key pada CustomerController (kombinasi type)
        $customers = Cache::remember($customerChaceKey, 300, function () {
            return Customer::orderBy('fullname', 'asc')->get();
        });

        // Ambil semua metode antar/jemput
        $deliveryMethodCacheKey = 'delivery_method_cache_key'; // Pastikan sama dengan cache key pada DeliveryMethodController 
        $deliveryMethods = Cache::remember($deliveryMethodCacheKey, 300, function () {
            return DeliveryMethod::orderBy('created_at', 'desc')->get();
        })->where('active', true)->sortBy('method_name');

        // Return ke halaman create
        return view('dashboard.order.manage-order.create', compact('order', 'details', 'customers', 'services', 'deliveryMethods'));
    }

    // Tidak dapat diakses
    public function store()
    {
        $this->isNotAllowed(['owner', 'admin', 'employee', 'customer']);
    }

    // Mengubah order yang telah dibuat
    public function edit($id)
    {
        $user = Auth::user();
        $customer = $user->relatedCustomer;
        $staff = $user->relatedStaff;

        $order = Order::findOrFail($id);

        // Cegah pelanggan mengedit jika status order bukan "new"
        if ($customer && $order->order_status !== 'new') {
            return redirect()->back()->with('warning', 'Pesanan tidak dapat diubah.');
        }

        // Ambil detail layanan dalam order, beserta layanan dan promo yang terkait
        $details = OrderServiceDetail::with([
            'parentOrder',
            'includedService.promos', // promos lewat relasi many-to-many di Service
            'includedPromo' // promo_id yang sudah dipilih sebelumnya
        ])->where('order_id', $order->id)->get();

        // Siapkan array promo sesuai dengan masing-masing detail
        $promos = [];

        if ($staff) {
            foreach ($details as $detail) {
                $promos[$detail->id] = $detail->includedService?->promos ?? collect();
            }
        } else {
            // Kosongkan promo jika bukan staff (tidak perlu tampilkan di view)
            foreach ($details as $detail) {
                $promos[$detail->id] = collect();
            }
        }

        // Ambil data lain
        $deliveryMethods = [];
        $customers = [];
        $services = [];

        if (!$details->isEmpty()) {
            $deliveryMethodCacheKey = 'delivery_method_cache_key'; // Pastikan sama dengan cache key pada DeliveryMethodController
            $deliveryMethods = Cache::remember($deliveryMethodCacheKey, 300, function () {
                return DeliveryMethod::orderBy('created_at', 'desc')->get();
            })->where('active', true)->sortBy('method_name');

            if ($staff) {
                $customerChaceKey = 'customers_by_type_member_non_member'; // Pastikan sama dengan cache key pada CustomerController (kombinasi type)
                $customers = Cache::remember($customerChaceKey, 300, function () {
                    return Customer::orderBy('fullname', 'asc')->get();
                });

                $serviceCacheKey = 'service_cache_key'; // Pastikan sama dengan cache key pada ServiceController
                $services = Cache::remember($serviceCacheKey, 300, function () {
                    return Service::orderBy('created_at', 'desc')->get();
                })->where('active', true)->sortBy('service_name');
            }
        }

        return view('dashboard.order.manage-order.edit', compact(
            'order',
            'details',
            'promos',
            'deliveryMethods',
            'customers',
            'services',
        ));
    }

    // Update order yang telah dibuat
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $user = Auth::user();
        $customer = $user->relatedCustomer;
        $staff = $user->relatedStaff;

        $rules = [
            'staff_id' => 'nullable|exists:staffs,id',
            'delivery_method_id' => 'required|exists:delivery_methods,id',
            'delivery_cost' => 'required|numeric|min:0',
            'pickup_date' => 'nullable|date',
            'pickup_time' => 'nullable|date_format:H:i',
            'delivery_date' => 'nullable|date',
            'delivery_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ];

        // Jika staff yang login
        if ($staff) {
            $rules['customer_id'] = 'required|exists:customers,id';
            $rules['order_status'] = 'required|in:new,pending,pickup,in_progress,delivery,done,canceled';
        }

        $messages = [
            'customer_id.required' => 'Pelanggan wajib dipilih.',
            'customer_id.exists' => 'Pelanggan tidak valid.',
            'delivery_method_id.required' => 'Metode antar/jemput wajib dipilih.',
            'delivery_method_id.exists' => 'Metode antar/jemput tidak valid.',
            'delivery_cost.required' => 'Biaya antar/jemput wajib diisi.',
            'delivery_cost.numeric' => 'Biaya antar/jemput harus berupa angka.',
            'pickup_date.date' => 'Tanggal jemput tidak valid.',
            'pickup_time.date_format' => 'Format waktu jemput tidak valid.',
            'delivery_date.date' => 'Tanggal antar tidak valid.',
            'delivery_time.date_format' => 'Format waktu antar tidak valid.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
            'staff_id.exists' => 'Petugas tidak valid.',
            'order_status.required' => 'Status pesanan wajib dipilih.',
            'order_status.in' => 'Status pesanan tidak valid.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            DB::beginTransaction();

            // Cek order_code apakah null atau tidak
            $isNewOrder = $order->order_code === null;
            $orderCode = $isNewOrder
                ? 'ORD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(5))
                : $order->order_code;

            $createdAt = $isNewOrder ? now() : $order->created_at;

            if ($customer) {
                $message = 'Pesanan berhasil diperbarui. Silakan menunggu konfirmasi dari Admin.';
            } elseif ($staff) {
                $message = $isNewOrder
                    ? 'Pesanan berhasil dibuat.'
                    : 'Pesanan berhasil diperbarui.';
            }

            // Ambil semua detail dari order ini
            $details = $order->orderDetails;
            $allFinalServicePrice = 0; // Inisialisasi harga akhir

            // Akumulasi semua harga akhir pada setiap layanan
            foreach ($details as $detail) {
                $allFinalServicePrice += $detail->final_service_price;
            }

            $totalServicePrice = $allFinalServicePrice;

            $order->update([
                'order_code' => $orderCode,
                'customer_id' => $customer ? $customer->id : $data['customer_id'],
                'staff_id' => $staff ? ($order->staff_id === null ? $staff->id : $order->staff_id) : null,
                'delivery_method_id' => $data['delivery_method_id'],
                'delivery_cost' => $data['delivery_cost'],
                'total_service_price' => $totalServicePrice,
                'pickup_date' => $data['pickup_date'] ?? null,
                'pickup_time' => $data['pickup_time'] ?? null,
                'delivery_date' => $data['delivery_date'] ?? null,
                'delivery_time' => $data['delivery_time'] ?? null,
                'order_status' => $customer ? $order->order_status : $data['order_status'],
                'notes' => $data['notes'] ?? null,
                'created_at' => $createdAt,
            ]);

            DB::commit();

            $this->clearAllOrderCache();
            $this->clearAllScheduleCache();

            if ($staff) {
                $customerData = $order?->orderingCustomer;

                $fullname = $customerData->fullname ?? 'N/A';
                $phone_number = $customerData->phone_number ? formatPhoneNumber($customerData->phone_number) : 'N/A';

                // Pesan berdasarkan status pesanan
                $statusMessages = [
                    'new' => "Pesanan Anda telah dibuat. Kami akan segera menindaklanjutinya.",
                    'pending' => "Pesanan Anda telah diterima oleh Admin. Silakan menunggu konfirmasi selanjutnya.",
                    'pickup' => "Pesanan Anda akan segera dijemput oleh kurir kami.",
                    'in_progress' => "Pesanan Anda sedang diproses. Mohon tunggu hingga pesanan Anda siap.",
                    'done' => "Pesanan Anda telah selesai. Terima kasih telah menggunakan layanan kami.",
                    'delivery' => "Pesanan Anda sedang dalam perjalanan untuk diantar.",
                    'canceled' => "Pesanan Anda telah dibatalkan.",
                ];

                // Menyiapkan pesan untuk notifikasi
                $messageText = $statusMessages[$data['order_status']] ?? 'Status pesanan telah diperbarui.';
                $message = "Hai {$fullname}, {$messageText}";
                $messageUrl = "https://wa.me/{$phone_number}?text=" . urlencode($message);

                return redirect(url('/order'))->with('success-with-url', 'Status pesanan berhasil diperbarui.')->with('url', $messageUrl);
            }

            return redirect('/order')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Pesanan gagal diperbarui. Silakan coba kembali.');
        }
    }

    // Menghapus order (Owner, Admin)
    public function destroy($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $order = Order::with([
            'orderTransactions.relatedProof',
            'orderDetails'
        ])->findOrFail($id);

        try {
            DB::beginTransaction();

            // Hapus bukti pembayaran dan file gambar
            if ($order->orderTransactions) {
                foreach ($order->orderTransactions as $transaction) {
                    if ($transaction->relatedProof) {
                        $proof = $transaction->relatedProof;

                        if ($proof->img) {
                            $path = public_path('img/uploads/proofs/' . $proof->img);
                            if (is_file($path) && is_readable($path)) {
                                @unlink($path);
                            }
                        }

                        $proof->delete(); // ProofOfPayment tidak pakai soft delete, jadi cukup delete
                    }

                    $order->orderTransaction->forceDelete(); // Transaction pakai soft delete
                }
            }

            // Hapus detail order
            foreach ($order->orderDetails as $detail) {
                $detail->forceDelete();
            }

            $order->forceDelete();

            DB::commit();

            $this->clearAllOrderCache();
            $this->clearAllScheduleCache();

            return redirect(url('/order'))->with('success', 'Pesanan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Pesanan gagal dihapus. Silakan coba kembali.');
        }
    }

    // Menambahkan layanan pada order yang telah dibuat (Owner, Admin, Employee)
    public function storeServiceToOrder(Request $request, $id)
    {
        $this->isNotAllowed(['customer']);

        // Validasi input service_id, pastikan wajib dan ada di tabel services
        $request->validate([
            'service_id' => 'required|exists:services,id',
        ], [
            'service_id.required' => 'Silakan pilih layanan yang ingin ditambahkan.',
            'service_id.exists' => 'Layanan yang dipilih tidak ditemukan.',
        ]);

        // Ambil order berdasarkan ID
        $order = Order::findOrFail($id);

        // Ambil data layanan berdasarkan ID
        $service = Service::findOrFail($request->input('service_id'));

        // Pastikan layanan aktif
        if (!$service->active) {
            return back()->withErrors(['service_id' => 'Layanan tidak tersedia.'])->withInput();
        }

        // Cek apakah layanan sudah pernah ditambahkan ke order ini
        $exists = OrderServiceDetail::where('order_id', $order->id)
            ->where('service_id', $service->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['service_id' => 'Layanan sudah ada dalam pesanan.'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Simpan detail layanan ke order
            OrderServiceDetail::create([
                'order_id' => $order->id,
                'service_id' => $service->id,
                'weight_kg' => 0, // Default berat 0 kg
                'price_per_kg' => $service->price_per_kg, // Harga sesuai layanan
                'promo_id' => null, // Promo kosong di awal
                'discount_percent' => 0, // Tidak ada diskon awal
            ]);

            DB::commit();

            $this->clearAllOrderCache();
            $this->clearAllScheduleCache();

            return back()->with('success', 'Layanan berhasil ditambahkan.');
        } catch (\Exception $e) {

            Log::error('Gagal menambahkan layanan', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Layanan gagal ditambahkan. Silakan coba kembali.');
        }
    }


    // Update semua layanan dari order yang telah dibuat (Owner, Admin, Employee)
    public function updateServicesFromOrder($id)
    {
        $this->isNotAllowed(['customer']);

        $order = Order::findOrFail($id);

        // Ambil semua detail dari order ini
        $details = $order->orderDetails;

        try {
            foreach ($details as $detail) {
                $detailId = $detail->id;

                // Ambil input dari form
                $weight = request()->input("weight_kg.$detailId");
                $promoId = request()->input("promo_id.$detailId");


                // Validasi input
                $data = Validator::make(request()->all(), [
                    "weight_kg.$detailId" => 'required|numeric|min:1',
                ], [
                    "weight_kg.$detailId.required" => "Berat/Jumlah tidak boleh kosong.",
                    "weight_kg.$detailId.numeric" => "Berat/Jumlah harus berupa angka.",
                    "weight_kg.$detailId.min" => "Berat/Jumlah minimal 1.",
                ]);

                if ($data->fails()) {
                    return redirect()->back()
                        ->withErrors($data)
                        ->withInput();
                }

                // Ambil harga layanan terbaru
                $service = $detail->includedService;
                $pricePerKg = $service?->price_per_kg ?? 0;

                // Ambil promo jika ada
                $promo = $service->promos->where('id', $promoId)->first();
                $discountPercent = $promo?->discount_percent ?? 0;

                // Update detail item
                $detail->update([
                    'weight_kg' => $weight,
                    'price_per_kg' => $pricePerKg,
                    'promo_id' => $promoId,
                    'discount_percent' => $discountPercent,
                ]);
            }

            $this->clearAllOrderCache();
            $this->clearAllScheduleCache();

            return redirect()->back()->with('success', 'Layanan berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui layanan', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Layanan gagal diperbarui. Silakan coba kembali.');
        }
    }

    // Hapus layanan dari order yang telah dibuat (Owner, Admin, Employee)
    public function destroyServiceFromOrder(Order $order, OrderServiceDetail $detail)
    {
        $this->isNotAllowed(['customer']);

        if ($detail->order_id !== $order->id) {
            return redirect()->back()->with('warning', 'Layanan tidak sesuai dengan order.');
        }

        $parentOrder = $detail->parentOrder;

        // Cegah penghapusan item jika status order bukan "new"
        if ($parentOrder->order_status !== 'new') {
            return redirect()->back()->with('warning', 'Layanan tidak dapat dihapus.');
        }

        try {
            DB::beginTransaction();

            // Hapus detail
            $detail->forceDelete();

            if ($order->orderDetails()->count() === 0) {
                $order->forceDelete();
                $url = url('/order');
                $message = 'Layanan terakhir telah dihapus. Pesanan juga dihapus karena tidak lagi memiliki layanan.';
            } else {
                $url = back();
                $message = 'Layanan berhasil dihapus dari pesanan.';
            }

            DB::commit();

            $this->clearAllOrderCache();
            $this->clearAllScheduleCache();

            return redirect($url)->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal menghapus layanan', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Layanan gagal dihapus. Silakan coba kembali.');
        }
    }

    // Menampilkan seluruh layanan (Customer)
    public function servicesOrder(Request $request)
    {
        $this->isAllowed(['customer']);

        $search = $request->input('search');

        $user = Auth::user();

        if ($user) {
            $customerId = $user->relatedCustomer->id;

            $currentOrder = Order::where('customer_id', $customerId)
                ->whereNull('order_code')
                ->where('order_status', 'new')
                ->with('orderDetails') // relasi ke order_service_details
                ->first();

            // Ambil semua ID layanan yang sudah ada di keranjang
            $addedServiceIds = $currentOrder
                ? $currentOrder->orderDetails->pluck('service_id')->toArray()
                : [];
        } else {
            $addedServiceIds = [];
        }

        if ($search) {
            // Jika ada pencarian, ambil langsung dari database TANPA cache
            $services = Service::where('active', true)
                ->where('service_name', 'like', '%' . $search . '%')
                ->orderBy('service_name', 'asc')->get();
        } else {
            // Jika tidak ada pencarian, ambil dari cache
            $serviceCacheKey = 'service_cache_key'; // Pastikan sama dengan cache key pada ServiceController
            $services = Cache::remember($serviceCacheKey, 300, function () {
                return Service::orderBy('created_at', 'desc')->get();
            })->where('active', true)->sortBy('service_name');
        }

        return view('dashboard.order.selection.service', compact('services', 'addedServiceIds'));
    }

    // Menambahkan layanan ke keranjang (Customer)
    public function storeServiceToCartOrder($serviceId)
    {
        $this->isAllowed(['customer']);

        $user = Auth::user();
        $customerId = $user->relatedCustomer->id;

        try {
            DB::beginTransaction();

            // 1. Cek apakah sudah ada order 'draft' untuk customer ini (dengan lock untuk mencegah race condition)
            $order = Order::where('customer_id', $customerId)
                ->whereNull('order_code')
                ->where('order_status', 'new')
                ->lockForUpdate() // Kunci baris untuk mencegah race condition
                ->first();

            // 2. Jika belum ada, buat order baru
            if (!$order) {
                $order = Order::create([
                    'customer_id' => $customerId,
                    'order_status' => 'new',
                    'payment_status' => 'unpaid',
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal membuat pesanan baru', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Pesanan gagal dibuat. Silakan coba kembali.');
        }

        // 3. Ambil layanan dan cek status
        $service = Service::findOrFail($serviceId);

        if (!$service->active) {
            return redirect()->back()->with('error', 'Layanan tidak tersedia.');
        }

        // 4. Cek apakah layanan sudah ada di order
        $existingDetail = OrderServiceDetail::where('order_id', $order->id)
            ->where('service_id', $service->id)
            ->first();

        if ($existingDetail) {
            return redirect()->back()->with('warning', 'Layanan sudah ada di keranjang.');
        }

        try {
            DB::beginTransaction();

            // 5. Tambahkan layanan ke order_service_details
            OrderServiceDetail::create([
                'order_id' => $order->id,
                'service_id' => $service->id,
                'weight_kg' => 0,
                'price_per_kg' => $service->price_per_kg,
                'promo_id' => null,
                'discount_percent' => 0,
            ]);

            DB::commit();

            $this->clearAllOrderCache();
            $this->clearAllScheduleCache();

            return redirect()->back()->with('success', 'Layanan berhasil ditambahkan ke keranjang.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal menambahkan layanan ke keranjang', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return back()->with('error', 'Layanan gagal ditambahkan ke keranjang. Silakan coba kembali.');
        }
    }

    // Menampilkan semua layanan yang dipilih pada keranjang (Customer)
    public function cartOrder()
    {
        $this->isAllowed(['customer']);

        $user = Auth::user();
        $customerId = $user->relatedCustomer->id;

        // Ambil order dengan status 'new' dan order_code null milik customer
        $order = Order::where('customer_id', $customerId)
            ->whereNull('order_code')
            ->where('order_status', 'new')
            ->first();

        if ($order) {
            // Ambil seluruh detail layanan dari order tersebut
            $carts = OrderServiceDetail::with(['parentOrder', 'includedService', 'includedPromo'])
                ->where('order_id', $order->id)
                ->get();
        } else {
            $carts = collect(); // kosongkan jika tidak ada order
        }

        $deliveryMethods = [];

        if (!$carts->isEmpty()) {
            $deliveryMethodCacheKey = 'delivery_method_cache_key'; // Pastikan sama dengan cache key pada DeliveryMethodController 
            $deliveryMethods = Cache::remember($deliveryMethodCacheKey, 300, function () {
                return DeliveryMethod::orderBy('created_at', 'desc')->get();
            })->where('active', true)->sortBy('method_name');
        }

        return view('dashboard.order.selection.cart', compact('order', 'carts', 'deliveryMethods'));
    }

    // Hapus layanan dari keranjang (Customer)
    public function destroyServiceFromCartOrder($detailId)
    {
        $this->isAllowed(['customer']);

        $user = Auth::user();
        $customer = $user->relatedCustomer;
        $staff = $user->relatedStaff;

        $detail = OrderServiceDetail::where('id', $detailId)
            ->whereHas('parentOrder', function ($query) use ($customer, $staff) {
                if ($customer) {
                    $query->where('customer_id', $customer->id);
                } elseif ($staff) {
                    $query->where('staff_id', $staff->id);
                }

                $query->whereNull('order_code')->where('order_status', 'new');
            })
            ->first();

        if (!$detail) {
            return redirect()->back()->with('warning', 'Layanan tidak ditemukan.');
        }

        $order = $detail->parentOrder;

        // Cegah pelanggan menghapus item jika order sudah bukan status keranjang
        if ($customer && $order->customer_id === $customer->id) {
            if ($order->order_code !== null || $order->order_status !== 'new') {
                return redirect()->back()->with('error', 'Layanan tidak dapat dihapus.');
            }
        }

        try {
            DB::beginTransaction();

            // Hapus item dari keranjang
            $detail->forceDelete(); // atau delete()

            // Jika order kosong setelah penghapusan, hapus order juga (opsional)
            if ($order->orderDetails()->count() === 0) {
                $order->forceDelete(); // atau delete()
            }

            DB::commit();

            $this->clearAllOrderCache();
            $this->clearAllScheduleCache();

            return redirect()->back()->with('success', 'Layanan berhasil dihapus dari keranjang.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal menghapus layanan dari keranjang', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return back()->with('error', 'Layanan gagal dihapus dari keranjang. Silakan coba kembali.');
        }
    }

    // Checkout order (Customer)
    public function checkoutOrder(Request $request, $id)
    {
        $this->isAllowed(['customer']);

        $order = Order::findOrFail($id);

        $user = Auth::user();
        $customer = $user->relatedCustomer;

        $rules = [
            'delivery_method_id' => 'required|exists:delivery_methods,id',
            'delivery_cost' => 'required|numeric|min:0',
            'pickup_date' => 'nullable|date',
            'pickup_time' => 'nullable|date_format:H:i',
            'delivery_date' => 'nullable|date',
            'delivery_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ];

        $messages = [
            'customer_id.required' => 'Pelanggan wajib dipilih.',
            'customer_id.exists' => 'Pelanggan tidak valid.',
            'delivery_method_id.required' => 'Metode antar/jemput wajib dipilih.',
            'delivery_method_id.exists' => 'Metode antar/jemput tidak valid.',
            'delivery_cost.required' => 'Biaya antar/jemput wajib diisi.',
            'delivery_cost.numeric' => 'Biaya antar/jemput harus berupa angka.',
            'pickup_date.date' => 'Tanggal jemput tidak valid.',
            'pickup_time.date_format' => 'Format waktu jemput tidak valid.',
            'delivery_date.date' => 'Tanggal antar tidak valid.',
            'delivery_time.date_format' => 'Format waktu antar tidak valid.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            DB::beginTransaction();

            $order->update([
                'order_code' => 'ORD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(5)),
                'customer_id' => $customer ? $customer->id : $data['customer_id'],
                'staff_id' => null,
                'delivery_method_id' => $data['delivery_method_id'],
                'delivery_cost' => $data['delivery_cost'],
                'pickup_date' => $data['pickup_date'] ?? null,
                'pickup_time' => $data['pickup_time'] ?? null,
                'delivery_date' => $data['delivery_date'] ?? null,
                'delivery_time' => $data['delivery_time'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_at' => now(),
            ]);

            DB::commit();

            $this->clearAllOrderCache();
            $this->clearAllScheduleCache();

            return redirect(url('/order'))->with('success', 'Pesanan berhasil dibuat. Silakan menunggu konfirmasi dari Admin.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Pesanan gagal dibuat. Silakan coba kembali.');
        }
    }

    // Update status order
    public function updateOrderStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $user = Auth::user();
        $customer = $user?->role === 'customer';

        $data = $request->validate([
            'order_status' => [
                'required',
                $customer ? 'in:canceled' : 'in:new,pending,pickup,in_progress,delivery,done,canceled',
            ]
        ]);

        try {
            // Jika yang login customer, hanya bisa membatalkan pesanan jika status 'new'
            if ($customer && $order->order_status !== 'new') {
                return back()->with('error', 'Pesanan tidak dapat dibatalkan.');
            }

            $customerData = $order->orderingCustomer;
            if (!$customerData) {
                return back()->with('error', 'Data pelanggan tidak ditemukan.');
            }

            $fullname = $customerData->fullname;
            $phone_number = formatPhoneNumber($customerData->phone_number);

            // Pesan berdasarkan status pesanan
            $statusMessages = [
                'new' => "Pesanan Anda telah dibuat. Kami akan segera menindaklanjutinya.",
                'pending' => "Pesanan Anda telah diterima oleh Admin. Silakan menunggu konfirmasi selanjutnya.",
                'pickup' => "Pesanan Anda akan segera dijemput oleh kurir kami.",
                'in_progress' => "Pesanan Anda sedang diproses. Mohon tunggu hingga pesanan Anda siap.",
                'done' => "Pesanan Anda telah selesai. Terima kasih telah menggunakan layanan kami.",
                'delivery' => "Pesanan Anda sedang dalam perjalanan untuk diantar.",
                'canceled' => "Pesanan Anda telah dibatalkan.",
            ];

            // Menyiapkan pesan untuk notifikasi
            $messageText = $statusMessages[$data['order_status']] ?? 'Status pesanan telah diperbarui.';
            $message = "Hai {$fullname}, {$messageText}";
            $messageUrl = "https://wa.me/{$phone_number}?text=" . urlencode($message);

            // Update status pesanan
            $order->update([
                'order_status' => $data['order_status'],
            ]);

            $this->clearAllOrderCache();
            $this->clearAllScheduleCache();

            if ($customer) {
                return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan.');
            }

            return redirect()->back()->with('success-with-url', 'Status pesanan berhasil diperbarui.')->with('url', $messageUrl);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Pesanan gagal diperbarui. Silakan coba kembali.');
        }
    }

    // Update status pembayaran
    public function updatePaymentStatus(Request $request, $orderId)
    {
        $this->isAllowed(['owner', 'admin']);

        $order = Order::findOrFail($orderId);

        $data = $request->validate([
            'payment_status' => [
                'required',
                'in:unpaid,partial,paid',
            ]
        ]);

        try {
            $customerData = $order->orderingCustomer;
            if (!$customerData) {
                return back()->with('error', 'Data pelanggan tidak ditemukan.');
            }

            $fullname = $customerData->fullname;
            $phone_number = formatPhoneNumber($customerData->phone_number);

            // Pesan berdasarkan status pembayaran
            $statusMessages = [
                'unpaid' => "Pembayaran belum diterima. Silakan lakukan pembayaran untuk memproses pesanan Anda.",
                'partial' => "Sebagian pembayaran telah diterima. Mohon selesaikan sisa pembayaran untuk melanjutkan proses pesanan.",
                'paid' => "Pembayaran telah diterima sepenuhnya. Terima kasih atas pembayaran Anda.",
            ];

            // Menyiapkan pesan untuk notifikasi
            $messageText = $statusMessages[$data['payment_status']] ?? 'Status pembayaran telah diperbarui.';
            $message = "Hai {$fullname}, {$messageText}";
            $messageUrl = "https://wa.me/{$phone_number}?text=" . urlencode($message);

            // Update status pembayaran
            $order->update([
                'payment_status' => $data['payment_status'],
            ]);

            $this->clearAllOrderCache();
            $this->clearAllScheduleCache();

            return redirect()->back()->with('success-with-url', 'Status pembayaran berhasil diperbarui.')->with('url', $messageUrl);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Pesanan gagal diperbarui. Silakan coba kembali.');
        }
    }

    // Cetak detail order
    public function printOrderDetail($id)
    {
        $order = Order::with(['orderingCustomer', 'deliveryOption', 'orderDetails'])->findOrFail($id);

        $customer = $order->orderingCustomer;
        $delivery = $order->deliveryOption;
        $details = $order->orderDetails;

        return view('dashboard.order.print.detail', compact('order', 'customer', 'delivery', 'details'));
    }

    // Cetak struk order
    public function printOrderReceipt($id)
    {
        $order = Order::with(['orderingCustomer', 'deliveryOption', 'orderDetails'])->findOrFail($id);

        $customer = $order->orderingCustomer;
        $delivery = $order->deliveryOption;
        $details = $order->orderDetails;
        $transactions = $order->orderTransactions()->where('status', 'success')->get();

        return view('dashboard.order.print.receipt', compact('order', 'customer', 'delivery', 'details', 'transactions'));
    }

    // Menampilkan jadwal antar/jemput
    public function pickupDeliverySchedule(Request $request)
    {
        $filter = $request->input('filter', 'all');
        $cacheKey = $this->getOrderCacheKey('schedule', $filter);

        $orders = Cache::remember($cacheKey, 300, function () use ($filter) {
            $query = Order::with(['orderingCustomer', 'deliveryOption'])->whereNotNull('order_code');

            $query->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('pickup_date')
                        ->whereMonth('pickup_date', Carbon::now()->month)
                        ->whereYear('pickup_date', Carbon::now()->year);
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('delivery_date')
                        ->whereMonth('delivery_date', Carbon::now()->month)
                        ->whereYear('delivery_date', Carbon::now()->year);
                });
            });

            if ($filter === 'today') {
                $query->where(function ($q) {
                    $q->whereDate('pickup_date', Carbon::today())
                        ->orWhereDate('delivery_date', Carbon::today());
                });
            } elseif ($filter === 'this_week') {
                $query->where(function ($q) {
                    $q->whereBetween('pickup_date', [
                        Carbon::now()->startOfWeek(Carbon::MONDAY),
                        Carbon::now()->endOfWeek(Carbon::SUNDAY),
                    ])->orWhereBetween('delivery_date', [
                        Carbon::now()->startOfWeek(Carbon::MONDAY),
                        Carbon::now()->endOfWeek(Carbon::SUNDAY),
                    ]);
                });
            }

            $query->orderByRaw("
            CASE 
                WHEN pickup_date IS NOT NULL AND delivery_date IS NOT NULL THEN 
                    CASE 
                        WHEN pickup_date <= delivery_date THEN pickup_date 
                        ELSE delivery_date 
                    END
                WHEN pickup_date IS NOT NULL THEN pickup_date
                WHEN delivery_date IS NOT NULL THEN delivery_date
                ELSE '9999-12-31'
            END ASC
            ");

            return $query->get();
        });

        // Jika customer (member) login, Filter berdasarkan id customer
        $user = Auth::user();
        $customerId = $user?->role === 'customer' ? $user?->relatedCustomer?->id : null;

        if ($customerId) {
            $orders = $orders->where('customer_id', $customerId);
        }

        return view('dashboard.order.schedule.index', ['orders' => $orders, 'filter' => $filter]);
    }
}

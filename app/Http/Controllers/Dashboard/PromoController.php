<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromoController extends Controller
{
    // Fungsi untuk menghapus cache
    protected function clearPromoCache()
    {
        foreach (['daily', 'date_range', 'all'] as $type) {
            foreach (['member', 'non_member', 'all'] as $scope) {
                $key = "promo_cache_{$type}_{$scope}";
                Cache::forget($key);
            }
        }
    }

    public function index(Request $request)
    {
        $promoType = $request->get('promo_type', 'all');
        $customerScope = $request->get('customer_scope', 'all');

        // Buat cache key berdasarkan filter
        $cacheKey = "promo_cache_{$promoType}_{$customerScope}";

        $promos = Cache::remember($cacheKey, 300, function () use ($promoType, $customerScope) {
            $query = Promo::query();

            if ($promoType !== 'all') {
                $query->where('promo_type', $promoType);
            }

            if ($customerScope !== 'all') {
                $query->where('customer_scope', $customerScope);
            }

            return $query->orderBy('created_at', 'desc')->get();
        });

        return view('dashboard.promo.index', compact('promos', 'promoType', 'customerScope'));
    }

    public function create()
    {
        $this->isAllowed(['owner', 'admin']);

        return view('dashboard.promo.create');
    }

    public function store(Request $request)
    {
        $this->isAllowed(['owner', 'admin']);

        $rules = [
            'promo_name' => 'required|string|max:100',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'promo_type' => 'required|in:daily,date_range',
            'customer_scope' => 'required|in:member,non_member',
            'description' => 'nullable|string',
            'active' => 'required|in:1,0',
        ];

        if ($request->input('promo_type') === 'daily') {
            $rules['day_of_week'] = 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday';
        } elseif ($request->input('promo_type') === 'date_range') {
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
        }

        $messages = [
            'promo_name.required' => 'Nama Promo wajib diisi.',
            'promo_name.string' => 'Nama Promo harus berupa teks.',
            'promo_name.max' => 'Nama Promo maksimal 100 karakter.',

            'discount_percent.required' => 'Diskon wajib diisi.',
            'discount_percent.numeric' => 'Diskon harus berupa angka.',
            'discount_percent.min' => 'Diskon tidak boleh kurang dari 0.',
            'discount_percent.max' => 'Diskon tidak boleh lebih dari 100.',

            'promo_type.required' => 'Tipe promo wajib dipilih.',
            'promo_type.in' => 'Tipe promo tidak valid.',

            'day_of_week.required' => 'Hari promo wajib dipilih.',
            'day_of_week.in' => 'Hari promo tidak valid.',

            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',

            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.date' => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',

            'customer_scope.required' => 'Segmentasi pelanggan wajib dipilih.',
            'customer_scope.in' => 'Segmentasi pelanggan tidak valid.',

            'description.string' => 'Keterangan harus berupa teks.',

            'active.required' => 'Status promo wajib dipilih.',
            'active.in' => 'Status promo tidak valid.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            Promo::create([
                'promo_name' => $data['promo_name'],
                'discount_percent' => $data['discount_percent'],
                'promo_type' => $data['promo_type'] ?? null,
                'day_of_week' => $data['day_of_week'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'customer_scope' => $data['customer_scope'] ?? null,
                'description' => $data['description'] ?? null,
                'active' => $data['active'] ?? true,
            ]);

            $this->clearPromoCache();

            return redirect(url('/promo'))->with('success', "Data berhasil ditambahkan.");
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Data gagal ditambahkan. Silakan coba kembali.');
        }
    }

    public function edit($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $promo = Promo::findOrFail($id);

        return view('dashboard.promo.edit', compact('promo'));
    }

    public function update(Request $request, $id)
    {
        $this->isAllowed(['owner', 'admin']);

        $promo = Promo::findOrFail($id);

        $rules = [
            'promo_name' => 'required|string|max:100',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'promo_type' => 'required|in:daily,date_range',
            'customer_scope' => 'required|in:member,non_member',
            'description' => 'nullable|string',
            'active' => 'required|in:1,0',
        ];

        if ($request->input('promo_type') === 'daily') {
            $rules['day_of_week'] = 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday';
        } elseif ($request->input('promo_type') === 'date_range') {
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
        }

        $messages = [
            'promo_name.required' => 'Nama Promo wajib diisi.',
            'promo_name.string' => 'Nama Promo harus berupa teks.',
            'promo_name.max' => 'Nama Promo maksimal 100 karakter.',

            'discount_percent.required' => 'Diskon wajib diisi.',
            'discount_percent.numeric' => 'Diskon harus berupa angka.',
            'discount_percent.min' => 'Diskon tidak boleh kurang dari 0.',
            'discount_percent.max' => 'Diskon tidak boleh lebih dari 100.',

            'promo_type.required' => 'Tipe promo wajib dipilih.',
            'promo_type.in' => 'Tipe promo tidak valid.',

            'day_of_week.required' => 'Hari promo wajib dipilih.',
            'day_of_week.in' => 'Hari promo tidak valid.',

            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',

            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.date' => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',

            'customer_scope.required' => 'Segmentasi pelanggan wajib dipilih.',
            'customer_scope.in' => 'Segmentasi pelanggan tidak valid.',

            'description.string' => 'Keterangan harus berupa teks.',

            'active.required' => 'Status promo wajib dipilih.',
            'active.in' => 'Status promo tidak valid.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            $promo->update([
                'promo_name' => $data['promo_name'],
                'discount_percent' => $data['discount_percent'],
                'promo_type' => $data['promo_type'] ?? null,
                'day_of_week' => $data['day_of_week'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'customer_scope' => $data['customer_scope'] ?? null,
                'description' => $data['description'] ?? null,
                'active' => $data['active'] ?? true,
            ]);

            $this->clearPromoCache();

            return redirect(url('/promo'))->with('success', "Data berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Data gagal diperbarui. Silakan coba kembali.');
        }
    }

    public function destroy($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $promo = Promo::findOrFail($id);

        try {
            if ($promo->services()->exists()) {
                // Menghapus relasi di tabel pivot (tanpa menghapus data di tabel services)
                $promo->services()->detach();
            }

            $promo->delete();

            $this->clearPromoCache();

            return redirect(url('/promo'))->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Data gagal dihapus. Silakan coba kembali.');
        }
    }

    public function managePromoService($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $promo = Promo::with('services')->findOrFail($id);

        $serviceCacheKey = 'service_cache_key'; // Pastikan sama dengan cache key pada ServiceController
        $services = Cache::remember($serviceCacheKey, 300, function () {
            return Service::orderBy('created_at', 'desc')->get();
        })->sortBy('service_name');

        return view('dashboard.promo.manage-service', compact('promo', 'services'));
    }

    public function storePromoService(Request $request, $id)
    {
        $this->isAllowed(['owner', 'admin']);

        // Validasi input
        $rules = [
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
        ];

        $messages = [
            'service_ids.required' => 'Pilih setidaknya satu layanan.',
            'service_ids.*.exists' => 'Layanan yang dipilih tidak valid.',
        ];

        // Validasi data
        $data = $request->validate($rules, $messages);
        $serviceIds = $data['service_ids'];

        try {
            // Ambil semua service_id yang sudah terkait dengan promo ini
            $existingServiceIds = DB::table('promo_service')
                ->where('promo_id', $id)
                ->pluck('service_id')
                ->toArray();

            $duplicateServices = [];
            $addedServices = [];

            foreach ($serviceIds as $serviceId) {
                $serviceName = Service::where('id', $serviceId)->value('service_name');

                if (in_array($serviceId, $existingServiceIds)) {
                    $duplicateServices[] = $serviceName;
                } else {
                    DB::table('promo_service')->insert([
                        'service_id' => $serviceId,
                        'promo_id' => $id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $addedServices[] = $serviceName;
                }
            }

            // Buat pesan umpan balik
            if (!empty($duplicateServices)) {
                $duplicateList = implode(', ', $duplicateServices);
                $addedList = !empty($addedServices) ? implode(', ', $addedServices) : null;

                $message = "<p><i class='fa-solid fa-circle-xmark text-danger mr-1'></i> {$duplicateList} sudah ada dalam promo.</p>";
                if ($addedList) {
                    $message .= "<p><i class='fa-solid fa-circle-check text-success mr-1'></i> {$addedList} berhasil ditambahkan ke promo.</p>";
                }

                return back()->with('warning', $message);
            }

            // Bersihkan input lama
            session()->forget('_old_input');

            return redirect("/promo/{$id}/manage-service#pivot-table")->with('success', 'Layanan berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error saat menambahkan layanan ke promo: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat menambahkan layanan. Silakan coba lagi.');
        }
    }

    public function destroyPromoService(Request $request, $id)
    {
        $this->isAllowed(['owner', 'admin']);

        $serviceIds = $request->input('service_ids', []);

        if (empty($serviceIds)) {
            return redirect()->back()->with('error', 'Tidak ada layanan yang dipilih untuk dihapus.');
        }

        try {
            DB::beginTransaction();

            foreach ($serviceIds as $serviceId) {
                DB::table('promo_service')
                    ->where('service_id', $serviceId)
                    ->where('promo_id', $id)
                    ->delete();
            }

            DB::commit();

            session()->forget('_old_input');

            return redirect("/promo/{$id}/manage-service#pivot-table")->with('success', 'Layanan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Layanan gagal dihapus. Silakan coba kembali.');
        }
    }
}

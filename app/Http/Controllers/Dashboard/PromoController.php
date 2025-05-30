<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
}

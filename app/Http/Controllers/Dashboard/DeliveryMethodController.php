<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DeliveryMethodController extends Controller
{
    // Fungsi untuk menghapus cache
    protected function clearDeliveryMethodCache()
    {
        $key = 'delivery_method_cache_key';
        Cache::forget($key);
    }

    public function index()
    {
        $cacheKey = 'delivery_method_cache_key';

        $deliveryMethods = Cache::remember($cacheKey, 300, function () {
            return DeliveryMethod::orderBy('created_at', 'desc')->get();
        });

        return view('dashboard.delivery-method.index', compact('deliveryMethods'));
    }

    public function create()
    {
        $this->isAllowed(['owner', 'admin']);

        return view('dashboard.delivery-method.create');
    }

    public function store(Request $request)
    {
        $this->isAllowed(['owner', 'admin']);

        $rules = [
            'method_name' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'active' => 'required|boolean',
        ];

        $messages = [
            'method_name.required' => 'Nama metode wajib diisi.',
            'method_name.string' => 'Nama metode harus berupa teks.',
            'method_name.max' => 'Nama metode maksimal 255 karakter.',

            'cost.required' => 'Biaya pengiriman wajib diisi.',
            'cost.numeric' => 'Biaya pengiriman harus berupa angka.',
            'cost.min' => 'Biaya pengiriman tidak boleh kurang dari 0.',

            'description.string' => 'Keterangan harus berupa teks.',

            'active.required' => 'Status wajib dipilih.',
            'active.boolean' => 'Status tidak valid.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            DeliveryMethod::create([
                'method_name' => $data['method_name'],
                'cost' => $data['cost'] ?? 0,
                'description' => $data['description'] ?? null,
                'active' => $data['active'] ?? true,
            ]);

            $this->clearDeliveryMethodCache();

            return redirect(url('/delivery-method'))->with('success', "Data berhasil ditambahkan.");
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Data gagal ditambahkan. Silakan coba kembali.');
        }
    }

    public function edit($id)
    {
        $deliveryMethod = DeliveryMethod::findOrFail($id);

        return view('dashboard.delivery-method.edit', compact('deliveryMethod'));
    }

    public function update(Request $request, $id)
    {
        $this->isAllowed(['owner', 'admin']);

        $deliveryMethod = DeliveryMethod::findOrFail($id);

        $isProtected = $deliveryMethod->id === 1;

        $rules = $isProtected ? [
            'description' => 'nullable|string',
            'active' => 'required|boolean',
        ] : [
            'method_name' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'active' => 'required|boolean',
        ];

        $messages = [
            'method_name.required' => 'Nama metode wajib diisi.',
            'method_name.string' => 'Nama metode harus berupa teks.',
            'method_name.max' => 'Nama metode maksimal 255 karakter.',

            'cost.required' => 'Biaya pengiriman wajib diisi.',
            'cost.numeric' => 'Biaya pengiriman harus berupa angka.',
            'cost.min' => 'Biaya pengiriman tidak boleh kurang dari 0.',

            'description.string' => 'Keterangan harus berupa teks.',

            'active.required' => 'Status wajib dipilih.',
            'active.boolean' => 'Status tidak valid.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            $deliveryMethod->update($data);

            $this->clearDeliveryMethodCache();

            return redirect(url('/delivery-method'))->with('success', "Data berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Data gagal diperbarui. Silakan coba kembali.');
        }
    }

    public function destroy($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $deliveryMethod = DeliveryMethod::findOrFail($id);

        try {
            $isProtected = $deliveryMethod->id === 1;

            if ($isProtected) {
                return back()->with('warning', 'Metode antar/jemput ini tidak boleh dihapus.');
            } else {
                $deliveryMethod->delete();
            }

            $this->clearDeliveryMethodCache();

            return redirect(url('/delivery-method'))->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Data gagal dihapus. Silakan coba kembali.');
        }
    }
}

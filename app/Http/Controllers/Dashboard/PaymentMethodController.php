<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    // Fungsi untuk menghapus cache
    protected function clearPaymentMethodCache()
    {
        $key = 'payment_method_cache_key';
        Cache::forget($key);
    }

    public function index()
    {
        $cacheKey = 'payment_method_cache_key';

        $paymentMethods = Cache::remember($cacheKey, 300, function () {
            return PaymentMethod::orderBy('created_at', 'desc')->get();
        });

        return view('dashboard.payment-method.index', compact('paymentMethods'));
    }

    public function create()
    {
        $this->isAllowed(['owner', 'admin']);

        return view('dashboard.payment-method.create');
    }

    public function store(Request $request)
    {
        $this->isAllowed(['owner', 'admin']);

        $rules = [
            'method_name' => 'required|string|unique:payment_methods,method_name|max:255',
            'payment_type' => 'required|in:manual,online,bank_transfer',
            'description' => 'nullable|string',
            'img' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'active' => 'required|boolean',
        ];

        $messages = [
            'method_name.required' => 'Nama metode wajib diisi.',
            'method_name.string' => 'Nama metode harus berupa teks.',
            'method_name.unique' => 'Nama metode sudah ada.',
            'method_name.max' => 'Nama metode maksimal 255 karakter.',

            'payment_type.required' => 'Tipe pembayaran wajib dipilih.',
            'payment_type.in' => 'Tipe pembayaran harus salah satu dari: Manual, Online atau Bank Transfer.',

            'description.string' => 'Keterangan harus berupa teks.',

            'img.image' => 'Gambar harus berupa file gambar.',
            'img.mimes' => 'Gambar hanya boleh berformat PNG, JPG, JPEG, atau WEBP.',
            'img.max' => 'Ukuran maksimum gambar adalah 2MB.',

            'active.required' => 'Status wajib dipilih.',
            'active.boolean' => 'Status tidak valid.',
        ];

        $data = $request->validate($rules, $messages);

        if ($data['payment_type'] === 'online') {
            if ($request->hasFile('img')) {
                $file = $request->file('img');

                $timestamp = now()->format('Ymd_His');

                $extension = $file->getClientOriginalExtension();

                $filename = 'payment_method_' . $timestamp . '.' . $extension;

                $uploadPath = public_path('img/uploads/payment_methods'); // Path upload

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $file->move($uploadPath, $filename);

                $data['img'] = $filename;
            }
        }

        try {
            PaymentMethod::create([
                'method_name' => $data['method_name'],
                'payment_type' => $data['payment_type'],
                'description' => $data['description'] ?? null,
                'active' => $data['active'],
                'img'   => $data['img'] ?? null,
            ]);

            $this->clearPaymentMethodCache();

            return redirect(url('/payment-method'))->with('success', "Data berhasil ditambahkan.");
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Data gagal ditambahkan. Silakan coba kembali.');
        }
    }

    public function edit($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $paymentMethod = PaymentMethod::findOrFail($id);

        return view('dashboard.payment-method.edit', compact('paymentMethod'));
    }

    public function update(Request $request, $id)
    {
        $this->isAllowed(['owner', 'admin']);

        $paymentMethod = PaymentMethod::findOrFail($id);

        $isProtected = (
            ($paymentMethod->method_name === 'Cash' && $paymentMethod->payment_type === 'manual') ||
            ($paymentMethod->method_name === 'COD' && $paymentMethod->payment_type === 'manual')
        );

        $rules = $isProtected ? [
            'description' => 'nullable|string',
            'active' => 'required|boolean',
        ] : [
            'method_name' => [
                'required',
                'string',
                Rule::unique('payment_methods', 'method_name')->ignore($paymentMethod->id),
                'max:255',
            ],
            'payment_type' => 'required|in:manual,online,bank_transfer',
            'description' => 'nullable|string',
            'img' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'active' => 'required|boolean',
        ];

        $messages = [
            'method_name.required' => 'Nama metode wajib diisi.',
            'method_name.string' => 'Nama metode harus berupa teks.',
            'method_name.unique' => 'Nama metode sudah ada.',
            'method_name.max' => 'Nama metode maksimal 255 karakter.',

            'payment_type.required' => 'Tipe pembayaran wajib dipilih.',
            'payment_type.in' => 'Tipe pembayaran harus salah satu dari: Manual, Online atau Bank Transfer.',

            'description.string' => 'Keterangan harus berupa teks.',

            'img.image' => 'Gambar harus berupa file gambar.',
            'img.mimes' => 'Gambar hanya boleh berformat PNG, JPG, JPEG, atau WEBP.',
            'img.max' => 'Ukuran maksimum gambar adalah 2MB.',

            'active.required' => 'Status wajib dipilih.',
            'active.boolean' => 'Status tidak valid.',
        ];

        $data = $request->validate($rules, $messages);

        $uploadPath = public_path('img/uploads/payment_methods'); // Path upload

        if (!$isProtected && $data['payment_type'] === 'online') {
            // Upload img baru jika diisi
            if ($request->hasFile('img')) {
                $file = $request->file('img');

                $timestamp = now()->format('Ymd_His');

                $extension = $file->getClientOriginalExtension();

                $filename = 'payment_method_' . $timestamp . '.' . $extension;

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $file->move($uploadPath, $filename);

                // Hapus img lama jika ada dan file-nya benar-benar ada
                if (!empty($paymentMethod->img)) {
                    $oldPath = $uploadPath . '/' . $paymentMethod->img;
                    if (file_exists($oldPath) && is_file($oldPath) && is_readable($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                $data['img'] = $filename;
            } else {
                unset($data['img']); // Jangan update kolom img kalau tidak upload
            }
        } else {
            // Hapus img lama jika ada dan file-nya benar-benar ada
            if (!empty($paymentMethod->img)) {
                $oldPath = $uploadPath . '/' . $paymentMethod->img;
                if (file_exists($oldPath) && is_file($oldPath) && is_readable($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $data['img'] = null;
        }

        try {
            $paymentMethod->update($data);

            $this->clearPaymentMethodCache();

            return redirect(url('/payment-method'))->with('success', "Data berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Data gagal diperbarui. Silakan coba kembali.');
        }
    }

    public function destroy($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $paymentMethod = PaymentMethod::findOrFail($id);

        $isProtected = (
            ($paymentMethod->method_name === 'Cash' && $paymentMethod->payment_type === 'manual') ||
            ($paymentMethod->method_name === 'COD' && $paymentMethod->payment_type === 'manual')
        );

        if ($isProtected) {
            return back()->with('warning', 'Metode Pembayaran ini tidak boleh dihapus.');
        }

        try {
            $paymentMethod->delete();
            $this->clearPaymentMethodCache();

            return redirect(url('/payment-method'))->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Data gagal dihapus. Silakan coba kembali.');
        }
    }
}

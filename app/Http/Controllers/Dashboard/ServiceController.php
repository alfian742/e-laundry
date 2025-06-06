<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    // Fungsi untuk menghapus cache
    protected function clearServiceCache()
    {
        $key = 'service_cache_key';
        Cache::forget($key);
    }

    public function index()
    {
        $this->isAllowed(['owner', 'admin', 'employee']);

        $cacheKey = 'service_cache_key';

        $services = Cache::remember($cacheKey, 300, function () {
            return Service::orderBy('created_at', 'desc')->get();
        });

        return view('dashboard.service.index', compact('services'));
    }

    public function show($id)
    {
        $service = Service::with('promos')->findOrFail($id);

        $promoService = $service->promos->sortByDesc(function ($promo) {
            return $promo->pivot->created_at;
        })->where('active', true);

        return view('dashboard.service.show', compact('service', 'promoService'));
    }

    public function create()
    {
        $this->isAllowed(['owner', 'admin']);

        return view('dashboard.service.create');
    }

    public function store(Request $request)
    {
        $this->isAllowed(['owner', 'admin']);

        $rules = [
            'service_name' => 'required|string|max:255',
            'img' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048',
            'price_per_kg' => 'required|numeric|min:0',
            'description' => 'required|string',
            'active' => 'required|boolean',
        ];

        $messages = [
            'service_name.required' => 'Nama layanan wajib diisi.',
            'service_name.string' => 'Nama layanan harus berupa teks.',
            'service_name.max' => 'Nama layanan maksimal 255 karakter.',

            'price_per_kg.required' => 'Harga wajib diisi.',
            'price_per_kg.numeric' => 'Harga harus berupa angka.',
            'price_per_kg.min' => 'Harga tidak boleh kurang dari 0.',

            'description.required' => 'Deskripsi wajib diisi.',
            'description.string' => 'Deskripsi harus berupa teks.',

            'active.required' => 'Status wajib dipilih.',
            'active.boolean' => 'Status tidak valid.',

            'img.required' => 'Gambar wajib diunggah.',
            'img.image' => 'Gambar harus berupa file gambar.',
            'img.mimes' => 'Gambar hanya boleh berformat PNG, JPG, JPEG, atau WEBP.',
            'img.max' => 'Ukuran maksimum gambar adalah 2MB.',
        ];

        $data = $request->validate($rules, $messages);

        if ($request->hasFile('img')) {
            $file = $request->file('img');

            $timestamp = now()->format('Ymd_His');

            $extension = $file->getClientOriginalExtension();

            $filename = 'service_' . $timestamp . '.' . $extension;

            $uploadPath = public_path('img/uploads/services');

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $filename);

            $data['img'] = $filename;
        }

        try {
            Service::create($data);

            $this->clearServiceCache();

            return redirect(url('/service'))->with('success', 'Data berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Data gagal ditambahkan. Silakan coba kembali.');
        }
    }

    public function edit($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $service = Service::findOrFail($id);

        return view('dashboard.service.edit', compact('service'));
    }

    public function update(Request $request, $id)
    {
        $this->isAllowed(['owner', 'admin']);

        $service = Service::findOrFail($id);

        $rules = [
            'service_name' => 'required|string|max:255',
            'img' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'price_per_kg' => 'required|numeric|min:0',
            'description' => 'required|string',
            'active' => 'required|boolean',
        ];

        $messages = [
            'service_name.required' => 'Nama layanan wajib diisi.',
            'service_name.string' => 'Nama layanan harus berupa teks.',
            'service_name.max' => 'Nama layanan maksimal 255 karakter.',

            'price_per_kg.required' => 'Harga wajib diisi.',
            'price_per_kg.numeric' => 'Harga harus berupa angka.',
            'price_per_kg.min' => 'Harga tidak boleh kurang dari 0.',

            'description.required' => 'Deskripsi wajib diisi.',
            'description.string' => 'Deskripsi harus berupa teks.',

            'active.required' => 'Status wajib dipilih.',
            'active.boolean' => 'Status tidak valid.',

            'img.image' => 'Gambar harus berupa file gambar.',
            'img.mimes' => 'Gambar hanya boleh berformat PNG, JPG, JPEG, atau WEBP.',
            'img.max' => 'Ukuran maksimum gambar adalah 2MB.',
        ];

        $data = $request->validate($rules, $messages);

        // Upload img baru jika diisi
        if ($request->hasFile('img')) {
            $file = $request->file('img');

            $timestamp = now()->format('Ymd_His');

            $extension = $file->getClientOriginalExtension();

            $filename = 'service_' . $timestamp . '.' . $extension;

            $uploadPath = public_path('img/uploads/services');

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $filename);

            // Hapus img lama jika ada dan file-nya benar-benar ada
            if (!empty($service->img)) {
                $oldPath = $uploadPath . '/' . $service->img;
                if (file_exists($oldPath) && is_file($oldPath) && is_readable($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $data['img'] = $filename;
        } else {
            unset($data['img']); // Jangan update kolom img kalau tidak upload
        }

        try {
            $service->update($data);

            $this->clearServiceCache();

            return redirect(url('/service'))->with('success', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Data gagal diperbarui. Silakan coba kembali.');
        }
    }

    public function destroy($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $service = Service::findOrFail($id);

        try {
            if ($service->promos()->exists()) {
                // Menghapus relasi di tabel pivot (tanpa menghapus data di tabel promos)
                $service->promos()->detach();
            }

            // Hapus gambar jika ada dan file-nya benar-benar ada
            $uploadPath = public_path('img/uploads/services');

            if (file_exists($uploadPath)) {
                $oldPath = $uploadPath . '/' . $service->img;
                if (file_exists($oldPath) && is_file($oldPath) && is_readable($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $service->delete();

            $this->clearServiceCache();

            return redirect(url('/service'))->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Data gagal dihapus. Silakan coba kembali.');
        }
    }
}

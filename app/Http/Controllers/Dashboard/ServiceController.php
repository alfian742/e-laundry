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

            return redirect(url("/service/{$service->id}"))->with('success', 'Data berhasil diperbarui.');
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

    public function managePromoService($serviceId)
    {
        $this->isAllowed(['owner', 'admin']);

        // service
        $service = Service::findOrFail($serviceId);

        // pivot promo_service
        $promoService = $service->promos->sortByDesc(function ($promo) {
            return $promo->pivot->created_at;
        });

        // promos
        // Mendapatkan tanggal hari ini
        $today = Carbon::today();

        // Mengambil data promo yang aktif dari database
        $promos = Promo::where('active', true) // Memilih promo yang statusnya aktif
            ->where(function ($query) use ($today) { // Menambahkan kondisi tambahan untuk tipe promo
                $query->where('promo_type', 'daily') // Memilih promo dengan tipe 'daily' (harian)
                    ->orWhere(function ($subQuery) use ($today) { // Atau memilih promo dengan tipe 'date_range' (rentang tanggal)
                        $subQuery->where('promo_type', 'date_range') // Memilih promo dengan tipe 'date_range'
                            ->where(function ($dateQuery) use ($today) { // Menambahkan kondisi untuk rentang tanggal promo
                                $dateQuery->whereNull('end_date') // Memilih promo yang tidak memiliki tanggal akhir (end_date)
                                    ->orWhere('end_date', '>=', $today); // Atau promo yang tanggal akhirnya lebih besar atau sama dengan hari ini
                            });
                    });
            })
            ->orderBy('promo_name', 'asc') // Mengurutkan promo berdasarkan nama promo secara ascending (A-Z)
            ->get(); // Mengambil hasil query yang sudah difilter

        return view('dashboard.service.manage-promo', compact('service', 'promoService', 'promos'));
    }

    public function storePromoService(Request $request, $serviceId)
    {
        $this->isAllowed(['owner', 'admin']);

        $service = Service::findOrFail($serviceId);

        // Validasi input
        $data = $request->validate([
            'promo_id' => [
                'required',
                'exists:promos,id',
                Rule::unique('promo_service')->where(function ($query) use ($serviceId) {
                    return $query->where('service_id', $serviceId);
                }),
            ]
        ], [
            'promo_id.required' => 'Silakan pilih promo yang ingin ditambahkan.',
            'promo_id.exists' => 'Promo yang dipilih tidak ditemukan.',
            'promo_id.unique' => 'Promo sudah ditambahkan untuk layanan ini.'
        ]);


        try {
            // Tambahkan promo ke service
            $service->promos()->syncWithoutDetaching([$data['promo_id']]);

            $this->clearServiceCache();

            return redirect(url("/service/{$service->id}/manage-promo"))->with('success', 'Promo berhasil ditambahkan untuk layanan ini.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Promo gagal ditambahkan untuk layanan ini. Silakan coba kembali.');
        }
    }

    public function destroyPromoService($serviceId, $promoId)
    {
        $this->isAllowed(['owner', 'admin']);

        $service = Service::findOrFail($serviceId);

        try {
            // Hapus relasi pivot saja
            $service->promos()->detach($promoId);

            $this->clearServiceCache();

            return redirect(url("/service/{$service->id}/manage-promo"))->with('success', 'Promo berhasil dihapus dari layanan.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Promo gagal dihapus dari layanan. Silakan coba kembali.');
        }
    }
}

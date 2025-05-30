<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\SiteIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SiteIdentityController extends Controller
{
    public function index()
    {
        return view('dashboard.setting.index');
    }

    public function update(Request $request)
    {
        $site = SiteIdentity::first();

        $rules = [
            'site_name' => 'required|string|max:255',
            'tagline' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'email' => 'nullable|email',
            'phone_number' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    if (!str_starts_with($value, '08')) {
                        $fail('Nomor HP/WA tidak valid. Contoh: 081234567890');
                    }
                },
                'digits_between:10,15',
            ],
            'address' => 'required|string',
            'operational_hours' => 'required|string',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'tiktok' => 'nullable|url',
            'about_us' => 'nullable|string',
        ];

        $messages = [
            // site_name
            'site_name.required' => 'Nama situs wajib diisi.',
            'site_name.string' => 'Nama situs harus berupa teks.',
            'site_name.max' => 'Nama situs maksimal 255 karakter.',

            // tagline
            'tagline.required' => 'Tagline wajib diisi.',
            'tagline.string' => 'Tagline harus berupa teks.',
            'tagline.max' => 'Tagline maksimal 255 karakter.',

            // logo
            'logo.image' => 'Logo harus berupa file gambar.',
            'logo.mimes' => 'Logo hanya boleh berformat PNG, JPG, JPEG, atau WEBP.',
            'logo.max' => 'Ukuran maksimum logo adalah 2MB.',

            // email
            'email.email' => 'Email tidak valid.',

            // phone_number
            'phone_number.required' => 'Nomor HP/WA wajib diisi.',
            'phone_number.numeric' => 'Nomor HP/WA harus berupa angka.',
            'phone_number.digits_between' => 'Nomor HP/WA harus antara 10 hingga 15 digit.',

            // address
            'address.required' => 'Alamat usaha wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',

            // operational_hours
            'operational_hours.required' => 'Jam operasional wajib diisi.',
            'operational_hours.string' => 'Jam operasional harus berupa teks.',

            // facebook
            'facebook.url' => 'Format link Facebook tidak valid.',

            // instagram
            'instagram.url' => 'Format link Instagram tidak valid.',

            // tiktok
            'tiktok.url' => 'Format link TikTok tidak valid.',

            // about_us
            'about_us.string' => 'Tentang usaha harus berupa teks.',
        ];

        $data = $request->validate($rules, $messages);

        // Upload logo baru jika diisi
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');

            $timestamp = now()->format('Ymd_His');

            $extension = $file->getClientOriginalExtension();

            $filename = 'logo_' . $timestamp . '.' . $extension;

            $uploadPath = public_path('img/uploads/site');

            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $filename);

            // Hapus logo lama jika ada dan file-nya benar-benar ada
            if (!empty($site->logo)) {
                $oldPath = $uploadPath . '/' . $site->logo;
                if (file_exists($oldPath) && is_file($oldPath) && is_readable($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $data['logo'] = $filename;
        } else {
            unset($data['logo']); // Jangan update kolom logo kalau tidak upload
        }

        try {
            $site->update($data);

            // Pastikan key 'site_identity' konsisten dengan yang digunakan pada app/Providers/ViewServiceProvider.
            Cache::forget('site_identity');

            return redirect(url('/site-identity'))->with('success', 'Identitas situs berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat memperbarui identitas situs.');
        }
    }
}

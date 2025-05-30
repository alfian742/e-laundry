<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    // Fungsi untuk menghapus cache berdasarkan semua kombinasi posisi
    protected function clearStaffCache()
    {
        $positions = ['owner', 'admin', 'employee'];

        $positionCombinations = $this->getPositionCombinations($positions);

        foreach ($positionCombinations as $combo) {
            sort($combo);
            $key = 'staffs_by_position_' . implode('_', $combo);
            Cache::forget($key);
        }
    }

    // Fungsi helper untuk menghasilkan semua kombinasi posisi
    protected function getPositionCombinations($positions)
    {
        $results = [];
        $total = pow(2, count($positions));

        for ($i = 1; $i < $total; $i++) {
            $subset = [];
            for ($j = 0; $j < count($positions); $j++) {
                if ($i & (1 << $j)) {
                    $subset[] = $positions[$j];
                }
            }
            $results[] = $subset;
        }

        return $results;
    }

    // Fungsi umum untuk mengambil data staff berdasarkan posisi
    protected function getStaffsByPosition($positions)
    {
        sort($positions); // Supaya konsisten dengan cache key
        $cacheKey = 'staffs_by_position_' . implode('_', $positions);

        return Cache::remember($cacheKey, 300, function () use ($positions) {
            return Staff::whereIn('position', $positions)
                ->orderBy('fullname', 'asc')
                ->get();
        });
    }

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all'); // default ke 'all'

        // Tentukan posisi yang digunakan berdasarkan filter
        switch ($filter) {
            case 'admin':
                $positions = ['admin'];
                break;
            case 'employee':
                $positions = ['employee'];
                break;
            default: // 'all' atau lainnya
                $positions = ['owner', 'admin', 'employee'];
                break;
        }

        $staffs = $this->getStaffsByPosition($positions);

        return view('dashboard.master.staff.index', compact('staffs', 'filter'));
    }

    public function show($id)
    {
        $staff = Staff::findOrFail($id);

        return view('dashboard.master.staff.show', compact('staff'));
    }

    public function create()
    {
        $this->isAllowed(['owner', 'admin']);

        return view('dashboard.master.staff.create');
    }

    public function store(Request $request)
    {
        $this->isAllowed(['owner', 'admin']);

        $rules = [
            'fullname' => 'required|string|max:100',
            'position' => 'required|in:owner,admin,employee',
            'base_salary' => 'required|numeric|min:0',
            'bonus_salary' => 'nullable|numeric|min:0',
            'deductions_salary' => 'nullable|numeric|min:0',
            'address' => 'required|string|max:255',
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
        ];

        $messages = [
            'fullname.required' => 'Nama lengkap wajib diisi.',
            'fullname.string' => 'Nama lengkap harus berupa teks.',
            'fullname.max' => 'Nama lengkap maksimal 100 karakter.',

            'position.required' => 'Jabatan wajib dipilih.',
            'position.in' => 'Jabatan yang dipilih tidak valid. Pilih antara Admin atau Karyawan.',

            'base_salary.required' => 'Gaji pokok wajib diisi.',
            'base_salary.numeric' => 'Gaji pokok harus berupa angka.',
            'base_salary.min' => 'Gaji pokok tidak boleh kurang dari 0.',

            'bonus_salary.numeric' => 'Bonus gaji  harus berupa angka.',
            'bonus_salary.min' => 'Bonus gaji tidak boleh kurang dari 0.',

            'deductions_salary.numeric' => 'Potongan gaji harus berupa angka.',
            'deductions_salary.min' => 'Potongan gaji tidak boleh kurang dari 0.',

            'address.required' => 'Alamat wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 255 karakter.',

            'phone_number.required' => 'Nomor HP/WA wajib diisi.',
            'phone_number.numeric' => 'Nomor HP/WA harus berupa angka.',
            'phone_number.digits_between' => 'Nomor HP/WA harus antara 10 hingga 15 digit.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            Staff::create([
                'fullname' => $data['fullname'],
                'phone_number' => $data['phone_number'],
                'position' => $data['position'],
                'base_salary' => $data['base_salary'] ?? 0,
                'bonus_salary' => $data['bonus_salary'] ?? 0,
                'deductions_salary' => $data['deductions_salary'] ?? 0,
                'address' => $data['address'] ?? null,
            ]);

            $this->clearStaffCache();

            return redirect(url('/staff'))->with('success', "Data berhasil ditambahkan.");
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Data gagal ditambahkan. Silakan coba kembali.');
        }
    }

    public function edit($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $staff = Staff::findOrFail($id);

        return view('dashboard.master.staff.edit', compact('staff'));
    }

    public function update(Request $request, $id)
    {
        $this->isAllowed(['owner', 'admin']);

        $staff = Staff::findOrFail($id);

        $rules = [
            'fullname' => 'required|string|max:100',
            'position' => 'required|in:owner,admin,employee',
            'base_salary' => 'required|numeric|min:0',
            'bonus_salary' => 'nullable|numeric|min:0',
            'deductions_salary' => 'nullable|numeric|min:0',
            'address' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'numeric',
                'digits_between:10,15',
                function ($attribute, $value, $fail) {
                    if (!str_starts_with($value, '08')) {
                        $fail('Nomor HP/WA tidak valid. Contoh: 081234567890');
                    }
                }
            ],
        ];

        $messages = [
            'fullname.required' => 'Nama lengkap wajib diisi.',
            'fullname.string' => 'Nama lengkap harus berupa teks.',
            'fullname.max' => 'Nama lengkap maksimal 100 karakter.',

            'position.required' => 'Jabatan wajib dipilih.',
            'position.in' => 'Jabatan yang dipilih tidak valid. Pilih antara Admin atau Karyawan.',

            'base_salary.required' => 'Gaji pokok wajib diisi.',
            'base_salary.numeric' => 'Gaji pokok harus berupa angka.',
            'base_salary.min' => 'Gaji pokok tidak boleh kurang dari 0.',

            'bonus_salary.numeric' => 'Bonus gaji  harus berupa angka.',
            'bonus_salary.min' => 'Bonus gaji tidak boleh kurang dari 0.',

            'deductions_salary.numeric' => 'Potongan gaji harus berupa angka.',
            'deductions_salary.min' => 'Potongan gaji tidak boleh kurang dari 0.',

            'address.required' => 'Alamat wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 255 karakter.',

            'phone_number.required' => 'Nomor HP/WA wajib diisi.',
            'phone_number.numeric' => 'Nomor HP/WA harus berupa angka.',
            'phone_number.digits_between' => 'Nomor HP/WA harus antara 10 hingga 15 digit.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            $staff->update([
                'fullname' => $data['fullname'],
                'phone_number' => $data['phone_number'],
                'position' => $data['position'],
                'base_salary' => $data['base_salary'] ?? 0,
                'bonus_salary' => $data['bonus_salary'] ?? 0,
                'deductions_salary' => $data['deductions_salary'] ?? 0,
                'address' => $data['address'] ?? null,
            ]);

            if ($staff->userAccount) {
                $staff->userAccount->update([
                    'role' => $data['position'],
                ]);
            }

            $this->clearStaffCache();

            return redirect(url('/staff'))->with('success', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Data gagal diperbarui. Silakan coba kembali.');
        }
    }

    public function destroy($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $staff = Staff::findOrFail($id);

        try {
            if ($staff->position !== 'owner') {
                if ($staff->userAccount) {
                    $staff->userAccount->forceDelete();
                }

                $staff->delete();


                $this->clearStaffCache();


                // Cek apakah yang dihapus adalah akun yang sedang login
                if ($staff->userAccount && $staff->userAccount->id === Auth::user()->id) {
                    Auth::logout();
                    return redirect()->route('login')->with('success', 'Akun Anda telah dihapus. Silakan masuk dengan akun lain.');
                }
            } else {
                return back()->with('warning', 'Data pemilik tidak dapat dihapus. Silakan pilih data lainnya.');
            }

            return redirect(url('/staff'))->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Data gagal dihapus. Silakan coba kembali.');
        }
    }

    public function editProfile()
    {
        $this->isAllowed(['owner', 'admin', 'employee']);

        $user = Auth::user();

        $staff = Staff::whereHas('userAccount', function ($query) use ($user) {
            $query->where('id', $user->id);
        })->firstOrFail();

        return view('dashboard.master.staff.profile', compact('staff', 'user'));
    }

    public function updateProfile(Request $request)
    {
        $this->isAllowed(['owner', 'admin', 'employee']);

        $user = Auth::user();

        $staff = Staff::whereHas('userAccount', function ($query) use ($user) {
            $query->where('id', $user->id);
        })->firstOrFail();

        $user = $staff->userAccount;

        $rules = [
            'fullname' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
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
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password_old' => 'nullable',
            'password' => 'nullable|confirmed|min:8',
            'password_confirmation' => 'nullable|same:password|min:8',
        ];

        $messages = [
            'fullname.required' => 'Nama lengkap wajib diisi.',
            'fullname.string' => 'Nama lengkap harus berupa teks.',
            'fullname.max' => 'Nama lengkap maksimal 100 karakter.',

            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 255 karakter.',

            'phone_number.required' => 'Nomor HP/WA wajib diisi.',
            'phone_number.numeric' => 'Nomor HP/WA harus berupa angka.',
            'phone_number.digits_between' => 'Nomor HP/WA harus antara 10 hingga 15 digit.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',

            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sesuai.',
            'password_confirmation.same' => 'Konfirmasi kata sandi baru tidak sesuai.',
            'password_confirmation.min' => 'Konfirmasi kata sandi baru minimal 8 karakter.',
        ];

        $data = $request->validate($rules, $messages);

        // Handle password update
        if ($request->filled('password_old') && $request->filled('password') && $request->filled('password_confirmation')) {
            if (!Hash::check($request->password_old, $user->password)) {
                return redirect()->back()->withErrors(['password_old' => 'Kata sandi lama salah.']);
            }

            $user->password = Hash::make($data['password']);
        }

        try {
            // Update staff
            $staff->update([
                'fullname' => $data['fullname'],
                'phone_number' => $data['phone_number'],
                'address' => $data['address'] ?? null,
            ]);

            // Update User
            $user->email = $data['email'];
            $user->save();

            // Hapus cache
            $this->clearStaffCache();

            return redirect(url('/staff/profile'))->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Profil gagal diperbarui. Silakan coba kembali.');
        }
    }
}

<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    // Fungsi untuk menghapus cache berdasarkan semua kombinasi tipe pelanggan
    protected function clearCustomerCache()
    {
        $customerTypes = ['member', 'non_member'];

        $customerTypeCombinations = $this->getCustomerTypeCombinations($customerTypes);

        foreach ($customerTypeCombinations as $combo) {
            sort($combo);
            $key = 'customers_by_type_' . implode('_', $combo);
            Cache::forget($key);
        }
    }

    // Fungsi helper untuk menghasilkan semua kombinasi tipe pelanggan
    protected function getCustomerTypeCombinations($customerTypes)
    {
        $results = [];
        $total = pow(2, count($customerTypes));

        for ($i = 1; $i < $total; $i++) {
            $subset = [];
            for ($j = 0; $j < count($customerTypes); $j++) {
                if ($i & (1 << $j)) {
                    $subset[] = $customerTypes[$j];
                }
            }
            $results[] = $subset;
        }

        return $results;
    }

    // Fungsi umum untuk mengambil data customer berdasarkan tipe pelanggan
    protected function getCustomersByType($customerTypes)
    {
        sort($customerTypes); // Supaya konsisten dengan cache key
        $cacheKey = 'customers_by_type_' . implode('_', $customerTypes);

        return Cache::remember($cacheKey, 300, function () use ($customerTypes) {
            return Customer::whereIn('customer_type', $customerTypes)
                ->orderBy('fullname', 'asc')
                ->get();
        });
    }

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all'); // default ke 'all'

        // Tentukan tipe yang digunakan berdasarkan filter
        switch ($filter) {
            case 'member':
                $customerTypes = ['member'];
                break;
            case 'non_member':
                $customerTypes = ['non_member'];
                break;
            default: // 'all' atau lainnya
                $customerTypes = ['member', 'non_member'];
                break;
        }

        $customers = $this->getCustomersByType($customerTypes);

        return view('dashboard.master.customer.index', compact('customers', 'filter'));
    }

    public function show($id)
    {
        $customer = Customer::findOrFail($id);

        return view('dashboard.master.customer.show', compact('customer'));
    }

    public function create()
    {
        $this->isAllowed(['owner', 'admin']);

        return view('dashboard.master.customer.create');
    }

    public function store(Request $request)
    {
        $this->isAllowed(['owner', 'admin']);

        $rules = [
            'fullname' => 'required|string|max:100',
            'customer_type' => 'required|in:member,non_member',
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

            'customer_type.required' => 'Tipe pelanggan wajib dipilih.',
            'customer_type.in' => 'Tipe pelanggan yang dipilih tidak valid. Pilih antara Member atau Non-Member.',

            'address.required' => 'Alamat wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 255 karakter.',

            'phone_number.required' => 'Nomor HP/WA wajib diisi.',
            'phone_number.numeric' => 'Nomor HP/WA harus berupa angka.',
            'phone_number.digits_between' => 'Nomor HP/WA harus antara 10 hingga 15 digit.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            Customer::create([
                'fullname' => $data['fullname'],
                'phone_number' => $data['phone_number'],
                'customer_type' => $data['customer_type'],
                'address' => $data['address'] ?? null,
            ]);

            $this->clearCustomerCache();

            return redirect(url('/customer'))->with('success', "Data berhasil ditambahkan.");
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Data gagal ditambahkan. Silakan coba kembali.');
        }
    }

    public function edit($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $customer = Customer::findOrFail($id);

        return view('dashboard.master.customer.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $this->isAllowed(['owner', 'admin']);

        $customer = Customer::findOrFail($id);

        $rules = [
            'fullname' => 'required|string|max:100',
            'customer_type' => 'required|in:member,non_member',
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

            'customer_type.required' => 'Tipe pelanggan wajib dipilih.',
            'customer_type.in' => 'Tipe pelanggan yang dipilih tidak valid. Pilih antara Member atau Non-Member.',

            'address.required' => 'Alamat wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 255 karakter.',

            'phone_number.required' => 'Nomor HP/WA wajib diisi.',
            'phone_number.numeric' => 'Nomor HP/WA harus berupa angka.',
            'phone_number.digits_between' => 'Nomor HP/WA harus antara 10 hingga 15 digit.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            $customer->update([
                'fullname' => $data['fullname'],
                'phone_number' => $data['phone_number'],
                'customer_type' => $data['customer_type'],
                'base_salary' => $data['base_salary'] ?? 0,
                'bonus_salary' => $data['bonus_salary'] ?? 0,
                'deductions_salary' => $data['deductions_salary'] ?? 0,
                'address' => $data['address'] ?? null,
            ]);

            $this->clearCustomerCache();

            return redirect(url('/customer'))->with('success', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Data gagal diperbarui. Silakan coba kembali.');
        }
    }

    public function destroy($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $customer = Customer::findOrFail($id);

        try {
            if ($customer->userAccount) {
                $customer->userAccount->forceDelete();
            }

            $customer->delete();

            $this->clearCustomerCache();

            return redirect(url('/customer'))->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Data gagal dihapus. Silakan coba kembali.');
        }
    }

    public function editProfile()
    {
        $this->isAllowed(['customer']);

        $user = Auth::user();

        $customer = Customer::whereHas('userAccount', function ($query) use ($user) {
            $query->where('id', $user->id);
        })->firstOrFail();

        return view('dashboard.master.customer.profile', compact('customer', 'user'));
    }

    public function updateProfile(Request $request)
    {
        $this->isAllowed(['customer']);

        $user = Auth::user();

        $customer = Customer::whereHas('userAccount', function ($query) use ($user) {
            $query->where('id', $user->id);
        })->firstOrFail();

        $user = $customer->userAccount;

        $rules = [
            'fullname' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
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
            // Update customer
            $customer->update([
                'fullname' => $data['fullname'],
                'phone_number' => $data['phone_number'],
                'address' => $data['address'] ?? null,
            ]);

            // Update User
            $user->email = $data['email'];
            $user->save();

            // Hapus cache
            $this->clearCustomerCache();

            return redirect(url('/customer/profile'))->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Profil gagal diperbarui. Silakan coba kembali.');
        }
    }
}

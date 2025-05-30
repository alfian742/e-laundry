<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    // Fungsi untuk menghapus cache berdasarkan semua kombinasi peran
    protected function clearAccountCache()
    {
        $roles = ['owner', 'admin', 'employee', 'customer'];

        $roleCombinations = $this->getRoleCombinations($roles);

        foreach ($roleCombinations as $combo) {
            sort($combo);
            $key = 'accounts_by_role_' . implode('_', $combo);
            Cache::forget($key);
        }
    }

    // Fungsi helper untuk menghasilkan semua kombinasi peran
    protected function getRoleCombinations($roles)
    {
        $results = [];
        $total = pow(2, count($roles));

        for ($i = 1; $i < $total; $i++) {
            $subset = [];
            for ($j = 0; $j < count($roles); $j++) {
                if ($i & (1 << $j)) {
                    $subset[] = $roles[$j];
                }
            }
            $results[] = $subset;
        }

        return $results;
    }

    // Fungsi umum untuk mengambil data account berdasarkan peran
    protected function getAccountsByRole($roles)
    {
        sort($roles); // Supaya konsisten dengan cache key
        $cacheKey = 'accounts_by_role_' . implode('_', $roles);

        return Cache::remember($cacheKey, 300, function () use ($roles) {
            return User::whereIn('role', $roles)
                ->with(['relatedStaff', 'relatedCustomer'])
                ->get()
                ->sortBy(function ($user) {
                    return $user->relatedStaff->fullname
                        ?? $user->relatedCustomer->fullname
                        ?? '~'; // paling akhir secara alfabet
                })->values();
        });
    }

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all'); // default ke 'all'

        // Tentukan peran yang digunakan berdasarkan filter
        switch ($filter) {
            case 'admin':
                $roles = ['admin'];
                break;
            case 'employee':
                $roles = ['employee'];
                break;
            case 'customer':
                $roles = ['customer'];
                break;
            default: // 'all' atau lainnya
                $roles = ['owner', 'admin', 'employee', 'customer'];
                break;
        }

        $accounts = $this->getAccountsByRole($roles);

        return view('dashboard.master.account.index', compact('accounts', 'filter'));
    }

    public function create()
    {
        $this->isAllowed(['owner', 'admin']);

        $staffs = Staff::whereIn('position', ['admin', 'employee'])
            ->whereDoesntHave('userAccount') // hanya staff yang belum punya user
            ->orderBy('fullname', 'asc')
            ->get();

        $customers = Customer::where('customer_type', 'member')
            ->whereDoesntHave('userAccount') // hanya customer yang belum punya user
            ->orderBy('fullname', 'asc')
            ->get();

        return view('dashboard.master.account.create', compact('staffs', 'customers'));
    }


    public function store(Request $request)
    {
        $this->isAllowed(['owner', 'admin']);

        $rules = [
            'account_type' => 'required|in:staff,customer',
            'email' => 'required|email|unique:users,email',
            'status' => 'required|in:active,non_active',
        ];

        if ($request->input('account_type') === 'staff') {
            $rules['staff_id'] = 'required|unique:users,staff_id';
        } else {
            $rules['customer_id'] = 'required|unique:users,customer_id';
        }

        $messages = [
            'account_type.required' => 'Tipe akun wajib dipilih.',
            'account_type.in' => 'Tipe akun tidak valid. Pilih antara Staf dan Pelanggan.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',

            'staff_id.required' => 'Nama staf wajib dipilih.',
            'staff_id.unique' => 'Staf sudah terdaftar.',

            'customer_id.required' => 'Nama pelanggan wajib dipilih.',
            'customer_id.unique' => 'Pelanggan sudah terdaftar.',

            'status.required' => 'Status akun wajib dipilih.',
            'status.in' => 'Status akun tidak valid. Pilih antara Aktif dan Tidak Aktif.',
        ];

        $data = $request->validate($rules, $messages);

        // Tentukan role secara internal
        if ($data['account_type'] === 'staff') {
            $staff = Staff::findOrFail($data['staff_id']);
            $role = $staff->position; // pastikan ini sesuai dengan enum role di tabel users
        } else {
            $role = 'customer';
        }

        try {
            $password = 'skylaundry'; // Password default

            $account = User::create([
                'email' => $data['email'],
                'password' => Hash::make($password),
                'role' => $role,
                'staff_id' => $data['staff_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'status' => $data['status'],
                'verified_at' => now(),
            ]);

            $this->clearAccountCache();

            return redirect(url('/account'))->with('success', "Akun berhasil ditambahkan. Silakan masuk menggunakan email: '{$data['email']}' dan kata sandi: '{$password}'.");
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            if (isset($account)) {
                $account->delete();
            }

            return back()->with('error', 'Akun gagal ditambahkan. Silakan coba kembali.');
        }
    }

    public function edit($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $account = User::findOrFail($id);

        return view('dashboard.master.account.edit', compact('account'));
    }

    public function update(Request $request, $id)
    {
        $this->isAllowed(['owner', 'admin']);

        $account = User::findOrFail($id);

        $rules = [
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($account->id),
            ],
            'status' => 'required|in:active,non_active',
        ];

        $messages = [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',

            'status.required' => 'Status akun wajib dipilih.',
            'status.in' => 'Status akun tidak valid. Pilih antara Aktif dan Tidak Aktif.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            $account->update([
                'email' => $data['email'],
                'status' => $data['status'],
            ]);

            $this->clearAccountCache();

            return redirect(url('/account'))->with('success', 'Akun berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Akun gagal diperbarui. Silakan coba kembali.');
        }
    }

    public function destroy($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $account = User::findOrFail($id);

        try {
            if ($account->role !== 'owner') {
                $account->forceDelete();


                $this->clearAccountCache();

                // Cek apakah yang dihapus adalah akun yang sedang login
                if ($account->id === Auth::user()->id) {
                    Auth::logout();
                    return redirect()->route('login')->with('success', 'Akun Anda telah dihapus. Silakan masuk dengan akun lain.');
                }
            } else {
                return back()->with('warning', 'Akun pemilik tidak dapat dihapus. Silakan pilih akun lainnya.');
            }

            return redirect(url('/account'))->with('success', 'Akun berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return back()->with('error', 'Akun gagal dihapus. Silakan coba kembali.');
        }
    }

    public function resetAccount($id)
    {
        $this->isAllowed(['owner', 'admin']);

        $account = User::findOrFail($id);

        try {
            $email = $account->email ?? null;
            $password = 'skylaundry'; // Default password

            if (!is_null($account->staff_id) && $account->relatedStaff) {
                $fullname = $account->relatedStaff->fullname ?? 'N/A';
                $phone_number = formatPhoneNumber($account->relatedStaff->phone_number);
            } elseif (!is_null($account->customer_id) && $account->relatedCustomer) {
                $fullname = $account->relatedCustomer->fullname ?? 'N/A';
                $phone_number = formatPhoneNumber($account->relatedCustomer->phone_number);
            } else {
                return back()->with('warning', 'Akun tidak ditemukan.');
            }

            $account->update([
                'password' => Hash::make($password),
            ]);

            // Kirim pesan WhatsApp
            $message = "Hai {$fullname}, akun Anda sudah direset. Silakan masuk dengan email: '{$email}' dan kata sandi: '{$password}'. Terima kasih.";
            $message_urlencode = urlencode($message);
            $whatsapp_url = "https://wa.me/{$phone_number}?text={$message_urlencode}";

            // Hapus cache
            $this->clearAccountCache();

            return redirect(url('/account'))->with('success-with-url', 'Akun berhasil direset.')->with('url', $whatsapp_url);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());

            return back()->with('error', 'Akun gagal direset. Silakan coba kembali.');
        }
    }
}

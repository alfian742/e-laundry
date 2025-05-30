<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function authenticateLogin(Request $request)
    {
        // Cegah spam login (maks 10 kali per 5 menit per IP)
        $ip = $request->ip();
        $key = 'login_attempts:' . $ip;

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Terlalu banyak percobaan masuk. Coba lagi dalam {$seconds} detik.");
        }

        RateLimiter::hit($key, 300); // Membuat hit bertahan selama 5 menit

        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.'
        ]);

        $credentials = $request->only('email', 'password');

        try {
            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                if ($user->status !== 'active') {
                    Auth::logout();
                    return back()->with('error', 'Akun Anda tidak aktif.');
                }

                $request->session()->regenerate();

                $fullname = $user->staff_id !== null
                    ? optional($user->relatedStaff)->fullname
                    : optional($user->relatedCustomer)->fullname;

                // Hapus rate limiter jika berhasil login
                RateLimiter::clear($key);

                return redirect()->intended(url('/dashboard'))->with('success', "Selamat datang, {$fullname}.");
            }

            return back()->with('error', 'Periksa kembali email dan kata sandi Anda.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat masuk. Silahkan coba kembali.');
        }
    }

    public function register()
    {
        return view('auth.register');
    }

    public function authenticateRegister(Request $request)
    {
        $ip = $request->ip();
        $key = 'register_attempts:' . $ip;

        // Maks 5 percobaan per 5 menit
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Terlalu banyak percobaan pendaftaran. Coba lagi dalam {$seconds} detik.");
        }

        RateLimiter::hit($key, 300); // Membuat hit bertahan selama 5 menit

        $rules = [
            'fullname' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
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

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',

            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',

            'phone_number.required' => 'Nomor HP/WA wajib diisi.',
            'phone_number.numeric' => 'Nomor HP/WA harus berupa angka.',
            'phone_number.digits_between' => 'Nomor HP/WA harus antara 10 hingga 15 digit.',
        ];

        $data = $request->validate($rules, $messages);

        try {
            $customer = Customer::create([
                'fullname' => $data['fullname'],
                'phone_number' => $data['phone_number'],
                'customer_type' => 'member',
                'address' => null,
            ]);

            $user = User::create([
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'role' => 'customer',
                'staff_id' => null,
                'customer_id' => $customer->id,
                'status' => 'active',
                'verified_at' => now(),
            ]);

            // Hapus rate limiter setelah berhasil
            RateLimiter::clear($key);

            return redirect()->route('login')->with('success', 'Registrasi berhasil. Silakan masuk.');
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage()); // Check 'storage/logs/laravel.log'

            if (isset($customer)) $customer->delete();
            if (isset($user)) $user->delete();

            return redirect()->route('register')->with('error', 'Registrasi gagal. Silahkan coba kembali.');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout(); // Logout user

        $request->session()->invalidate();       // Invalidate session
        $request->session()->regenerateToken();  // Regenerate CSRF token

        return redirect()->route('login')->with('success', 'Anda berhasil keluar.');
    }
}

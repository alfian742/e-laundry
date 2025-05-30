<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\SiteIdentity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Memuat Helper secara manual
        require_once app_path('Helpers/Helper.php');

        View::composer('*', function ($view) {
            // Cek peran pengguna
            $user       = Auth::user();
            $isOwner    = $user && $user->role === 'owner';
            $isAdmin    = $user && $user->role === 'admin';
            $isEmployee = $user && $user->role === 'employee';
            $isCustomer = $user && $user->role === 'customer';

            // Zona waktu
            $offset = Carbon::now()->getOffset() / 3600;
            $zone = match ($offset) {
                7 => 'WIB',
                8 => 'WITA',
                9 => 'WIT',
                default => 'N/A',
            };

            // Mengambil data identitas situs dari cache; jika tidak ada, query ke database dan simpan ke cache selamanya.
            // Pastikan key 'site_identity' konsisten dengan Cache::forget('site_identity') di app/Http/Controllers/Site/SiteIdentityController.
            $site = Cache::rememberForever('site_identity', function () {
                return SiteIdentity::first();
            });


            // Hitung jumlah item di keranjang berdasarkan customer yang login
            $cartItemCount = 0;
            if ($isCustomer && $user->relatedCustomer) {
                $customerId = $user->relatedCustomer->id;

                $currentOrder = Order::where('customer_id', $customerId)
                    ->whereNull('order_code')
                    ->where('order_status', 'new')
                    ->with('orderDetails')
                    ->first();

                $cartItemCount = $currentOrder
                    ? $currentOrder->orderDetails->count()
                    : 0;
            }

            $view->with([
                // Cek peran pengguna
                'isOwner' => $isOwner,
                'isAdmin' => $isAdmin,
                'isEmployee' => $isEmployee,
                'isCustomer' => $isCustomer,

                // Zona waktu
                'zone' => $zone,

                // Situs
                'site' => $site,

                // Hitung jumlah item di keranjang
                'cartItemCount'  => $cartItemCount,
            ]);
        });
    }
}

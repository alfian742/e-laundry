<?php

namespace App\Http\Controllers\Landing;

use App\Http\Controllers\Controller;
use App\Models\CustomerReview;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $serviceCacheKey = 'service_cache_key'; // Pastikan sama dengan cache key pada ServiceController
        $services = Cache::remember($serviceCacheKey, 300, function () {
            return Service::orderBy('created_at', 'desc')->get();
        })->where('active', true)->sortBy('service_name')->take(6);


        $customerReviews = CustomerReview::with(['reviewingCustomer'])
            ->orderBy('review_at', 'desc')
            ->take(12)
            ->get();

        return view('landing.index', compact(['services', 'customerReviews']));
    }

    public function serviceForGuest(Request $request)
    {
        $search = $request->input('search');

        if ($search) {
            // Jika ada pencarian, ambil langsung dari database TANPA cache
            $services = Service::where('active', true)
                ->where('service_name', 'like', '%' . $search . '%')
                ->orderBy('service_name', 'asc')->get();
        } else {
            // Jika tidak ada pencarian, ambil dari cache
            $serviceCacheKey = 'service_cache_key'; // Pastikan sama dengan cache key pada ServiceController
            $services = Cache::remember($serviceCacheKey, 300, function () {
                return Service::orderBy('created_at', 'desc')->get();
            })->where('active', true)->sortBy('service_name');
        }

        return view('landing.service.index', compact('services'));
    }

    public function showServiceForGuest($id)
    {
        $service = Service::with('promos')->findOrFail($id);

        $promoService = $service->promos->sortByDesc(function ($promo) {
            return $promo->pivot->created_at;
        })->where('active', true);

        return view('landing.service.show', compact('service', 'promoService'));
    }

    public function checkOrder(Request $request)
    {
        $order_code = $request->input('order_code');

        $order = null;

        if ($order_code) {
            $order = Order::with('orderDetails')->where('order_code', $order_code)->first();
        }

        return view('landing.order.index', compact('order'));
    }
}

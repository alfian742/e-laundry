<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\AccountController;
use App\Http\Controllers\Dashboard\CustomerController;
use App\Http\Controllers\Dashboard\CustomerReviewController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\DeliveryMethodController;
use App\Http\Controllers\Dashboard\ExpenseController;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\PaymentMethodController;
use App\Http\Controllers\Dashboard\PromoController;
use App\Http\Controllers\Dashboard\ServiceController;
use App\Http\Controllers\Dashboard\StaffController;
use App\Http\Controllers\Dashboard\TransactionController;
use App\Http\Controllers\Landing\HomeController;
use App\Http\Controllers\Site\SiteIdentityController;
use Illuminate\Support\Facades\Route;

// Default
// Route::get('/', function () {
//     return view('welcome');
// });

// Custom

// ---------------- (Landing Page)
Route::controller(HomeController::class)->middleware('guest')->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/landing/service', 'serviceForGuest')->name('landing.service.index');
    Route::get('/landing/service/{service}', 'showServiceForGuest')->name('landing.service.show');
    Route::get('/landing/check-order', 'checkOrder')->name('landing.order.check');
});
// ---------------- 

// ---------------- (Auth)
// Login & Register
Route::controller(AuthController::class)->middleware('guest')->group(function () {
    Route::get('/login', 'index')->name('login');
    Route::post('/auth-login', 'authenticateLogin')->name('auth.login');
    Route::get('/register', 'register')->name('register');
    Route::post('/auth-register', 'authenticateRegister')->name('auth.register');
});
// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
// ---------------- 

// ---------------- (Dashboard)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

// View Notifikasi
Route::get('/notification', [DashboardController::class, 'notification'])
    ->name('notification')
    ->middleware('role:owner,admin,employee');

// Polling notifikasi via AJAX
Route::get('/get-notifications', [DashboardController::class, 'getNotifications'])
    ->name('notifications.get')
    ->middleware('role:owner,admin,employee');

// Statistic & Chart
Route::get('/statistic-order', [DashboardController::class, 'orderStatistic'])->name('statistic.order')->middleware('auth');
Route::get('/statistic-payment', [DashboardController::class, 'paymentStatistic'])->name('statistic.payment')->middleware('role:customer');
Route::get('/statistic-customer', [DashboardController::class, 'customerStatistic'])->name('statistic.customer')->middleware('role:owner,admin,employee');
Route::get('/statistic-staff', [DashboardController::class, 'staffStatistic'])->name('statistic.staff')->middleware('role:owner,admin,employee');
Route::get('/chart-revenue', [DashboardController::class, 'revenueChart'])->name('chart.revenue')->middleware('role:owner,admin,employee');
Route::get('/chart-expense', [DashboardController::class, 'expenseChart'])->name('chart.expense')->middleware('role:owner,admin,employee');
Route::get('/chart-finance', [DashboardController::class, 'financeChart'])->name('chart.finance')->middleware('role:owner,admin,employee');
Route::get('/chart-finance-years', [DashboardController::class, 'getAvailableFinanceYears'])->name('chart.finance.years')->middleware('role:owner,admin,employee');
Route::get('/chart-customer-review', [DashboardController::class, 'customerReviewChart'])->name('chart.customer.review')->middleware('role:owner,admin,employee');

// Clear Dashboard Cache
Route::get('/clear-dashboard-cache', [DashboardController::class, 'callToClearDashboardCache'])->name('clear.cache.dashboard')->middleware('auth');
// ----------------

// ---------------- (Site)
Route::controller(SiteIdentityController::class)->group(function () {
    Route::get('/site-identity', 'index')->name('site.identity.index')->middleware('role:owner,admin');
    Route::put('/site-identity', 'update')->name('site.identity.update')->middleware('role:owner,admin');
});
// ----------------

// ---------------- (Staff)
// Profile
Route::controller(StaffController::class)->group(function () {
    Route::get('/staff/profile', 'editProfile')->name('staff.profile.edit')->middleware('role:owner,admin,employee');
    Route::put('/staff/profile', 'updateProfile')->name('staff.profile.update')->middleware('role:owner,admin,employee');
});

// Resource
Route::resource('/staff', StaffController::class)->middleware('role:owner,admin,employee');
// ----------------

// ---------------- (Customer)
// Profile
Route::controller(CustomerController::class)->group(function () {
    Route::get('/customer/profile', 'editProfile')->name('customer.profile.edit')->middleware('role:customer');
    Route::put('/customer/profile', 'updateProfile')->name('customer.profile.update')->middleware('role:customer');
});

// Resource
Route::resource('/customer', CustomerController::class)->middleware('role:owner,admin,employee');
// ----------------

// ---------------- (Account)
// Reset
Route::put('/account/{id}/reset', [AccountController::class, 'resetAccount'])->name('account.reset')->middleware('role:owner,admin');
// Resource
Route::resource('/account', AccountController::class)->middleware('role:owner,admin,employee');
// ----------------

// ---------------- (Service)
// Manage Promo
Route::get('/service/{service}/manage-promo', [ServiceController::class, 'managePromoService'])->name('service.promo.manage')->middleware('role:owner,admin');
Route::post('/service/{service}/manage-promo', [ServiceController::class, 'storePromoService'])->name('service.promo.store')->middleware('role:owner,admin');
Route::delete('/service/{service}/destroy-promo/{promo}', [ServiceController::class, 'destroyPromoService'])->name('service.promo.destroy')->middleware('role:owner,admin');

// Resource
Route::resource('/service', ServiceController::class)->middleware('auth');
// ----------------

// ---------------- (Promo)
// Resource
Route::resource('/promo', PromoController::class)->middleware('role:owner,admin,employee');
// ----------------

// ---------------- (Delivery Method)
// Resource
Route::resource('/delivery-method', DeliveryMethodController::class)->middleware('role:owner,admin,employee');
// ----------------

// ---------------- (Payment Method)
// Resource
Route::resource('/payment-method', PaymentMethodController::class)->middleware('role:owner,admin,employee');
// ----------------

// ---------------- (Order)
Route::prefix('/order')->middleware('auth')->group(function () {
    // ---------------- (Manage order)
    // Menampilkan daftar layanan (hanya untuk customer)
    Route::get('/services', [OrderController::class, 'servicesOrder'])
        ->name('order.services')
        ->middleware('role:customer');

    // Menambahkan layanan ke keranjang (hanya untuk customer)
    Route::post('/cart/store/{service}', [OrderController::class, 'storeServiceToCartOrder'])
        ->name('order.cart.store')
        ->middleware('role:customer');

    // Melihat isi keranjang (hanya untuk customer)
    Route::get('/cart', [OrderController::class, 'cartOrder'])
        ->name('order.cart')
        ->middleware('role:customer');

    // Menghapus layanan dari keranjang (hanya untuk customer)
    Route::delete('/cart/destroy/{detail}', [OrderController::class, 'destroyServiceFromCartOrder'])
        ->name('order.cart.destroy')
        ->middleware('role:customer');

    // Checkout order (hanya untuk customer)
    Route::put('/checkout/{order}', [OrderController::class, 'checkoutOrder'])
        ->name('order.checkout')
        ->middleware('role:customer');

    // Menambahkan layanan ke order yang sudah ada (hanya untuk admin/owner/employee)
    Route::post('/{order}/store', [OrderController::class, 'storeServiceToOrder'])
        ->name('order.service.store')
        ->middleware('role:owner,admin,employee');

    // Mengupdate layanan dalam order yang sudah ada (hanya untuk admin/owner/employee)
    Route::put('/{order}/update', [OrderController::class, 'updateServicesFromOrder'])
        ->name('order.service.update')
        ->middleware('role:owner,admin,employee');

    // Menghapus layanan dari order yang sudah ada (hanya untuk admin/owner/employee)
    Route::delete('/{order}/destroy/{detail}', [OrderController::class, 'destroyServiceFromOrder'])
        ->name('order.service.destroy')
        ->middleware('role:owner,admin,employee');
    // ----------------

    // ---------------- (Transaction form order)
    Route::get('/{order}/transaction', [TransactionController::class, 'indexTransactionOrder'])
        ->name('transaction.order.index')
        ->middleware('role:owner,admin,customer');

    Route::get('/{order}/transaction/create', [TransactionController::class, 'createTransactionOrder'])
        ->name('transaction.order.create')
        ->middleware('role:owner,admin,customer');

    Route::post('/{order}/transaction', [TransactionController::class, 'storeTransactionOrder'])
        ->name('transaction.order.store')
        ->middleware('role:owner,admin,customer');

    Route::get('/{order}/transaction/{transaction}/edit', [TransactionController::class, 'editTransactionOrder'])
        ->name('transaction.order.edit')
        ->middleware('role:owner,admin,customer');

    Route::put('/{order}/transaction/{transaction}', [TransactionController::class, 'updateTransactionOrder'])
        ->name('transaction.order.update')
        ->middleware('role:owner,admin,customer');

    Route::delete('/{order}/transaction/{transaction}', [TransactionController::class, 'destroyTransactionOrder'])
        ->name('transaction.order.destroy')
        ->middleware('role:owner,admin');
    // ----------------

    // ---------------- (Print)
    // Print detail order
    Route::get('/{id}/print-detail', [OrderController::class, 'printOrderDetail'])
        ->name('order.print.detail')
        ->middleware('role:owner,admin,employee');

    // Print receipt
    Route::get('/{id}/print-receipt', [OrderController::class, 'printOrderReceipt'])
        ->name('order.print.receipt')
        ->middleware('role:owner,admin,employee');

    // Update status payment
    Route::put('/{id}/update-payment-status', [OrderController::class, 'updatePaymentStatus'])
        ->name('order.update.status.payment')
        ->middleware('role:owner,admin');

    // Update status order
    Route::put('/{id}/update-order-status', [OrderController::class, 'updateOrderStatus'])
        ->name('order.update.status');
    // ----------------
});

// Resource
Route::resource('/order', OrderController::class)->middleware('auth');

// Jadwal pickup/delivery
Route::get('/pickup-delivery-schedule', [OrderController::class, 'pickupDeliverySchedule'])
    ->name('order.schedule')
    ->middleware('auth');
// ----------------

// ---------------- (Finance)
// Pendapatam
Route::get('/revenue/print-report', [TransactionController::class, 'printReport'])->name('revenue.report')->middleware('role:owner,admin');
Route::get('/revenue/get-available-months', [TransactionController::class, 'getAvailableMonths'])->name('revenue.get.available.months')->middleware('role:owner,admin');
Route::get('/revenue', [TransactionController::class, 'index'])->name('revenue.index')->middleware('role:owner,admin');

// Pengeluaran
Route::get('/expense/print-report', [ExpenseController::class, 'printReport'])->name('expense.report')->middleware('role:owner,admin');
Route::get('/expense/get-available-months', [ExpenseController::class, 'getAvailableMonths'])->name('expense.get.available.months')->middleware('role:owner,admin');
Route::resource('/expense', ExpenseController::class)->middleware('role:owner,admin');
// ----------------

// ---------------- (Review)
Route::get('/customer-review/mark-all-as-read', [CustomerReviewController::class, 'markAllAsRead'])->name('customer-review.mark.all.as.read')->middleware('role:owner,admin');
Route::get('/customer-review/my-review', [CustomerReviewController::class, 'myReview'])->name('customer-review.my.review')->middleware('role:customer');
Route::resource('/customer-review', CustomerReviewController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->middleware('auth');
// ----------------
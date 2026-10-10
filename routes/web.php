<?php

use App\Http\Controllers\Admin\BoothController as AdminBoothController;
use App\Http\Controllers\Admin\SettlementController as AdminSettlementController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ProductController as VendorProductController;
use App\Http\Controllers\Vendor\SettlementController as VendorSettlementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ویترین عمومی سایت (بدون نیاز به لاگین)
|--------------------------------------------------------------------------
*/
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/about', [\App\Http\Controllers\PageController::class, 'about'])->name('about');
Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search');
Route::get('/category/{category:slug}', [\App\Http\Controllers\CategoryController::class, 'show'])->name('category.show');
Route::get('/booth/{booth:slug}', [\App\Http\Controllers\BoothController::class, 'show'])->name('booth.show');
Route::get('/product/{product}/modal', [\App\Http\Controllers\ProductController::class, 'modal'])->name('product.modal');
Route::post('/product/{product}/cart', [\App\Http\Controllers\ProductController::class, 'addToCart'])->name('product.cart.add');
Route::post('/product/{product}/cart-ajax', [\App\Http\Controllers\ProductController::class, 'addToCartAjax'])->name('product.cart.ajax');
Route::get('/product/{product}', [\App\Http\Controllers\ProductController::class, 'show'])->name('product.show');

/*
|--------------------------------------------------------------------------
| بازار املاک — زمین/ملک ثبت‌شده توسط زمین‌دارها که ادمین تأیید کرده باشد
|--------------------------------------------------------------------------
*/
Route::get('/properties', [\App\Http\Controllers\PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{property}', [\App\Http\Controllers\PropertyController::class, 'show'])->name('properties.show');
Route::post('/properties/{property}/inquiry', [\App\Http\Controllers\PropertyInquiryController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('properties.inquiry.store');

/*
|--------------------------------------------------------------------------
| آگهیِ درخواست ملک — برعکسِ properties: کسی که دنبال ملک/زمین می‌گرده
| (برای خرید، مشارکت یا تهاتر) خواسته‌ش رو عمومی ثبت می‌کنه تا مالکین ببینن.
|--------------------------------------------------------------------------
*/
Route::get('/property-requests', [\App\Http\Controllers\PropertyRequestController::class, 'index'])->name('property-requests.index');
Route::get('/property-requests/create', [\App\Http\Controllers\PropertyRequestController::class, 'create'])->name('property-requests.create');
Route::post('/property-requests', [\App\Http\Controllers\PropertyRequestController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('property-requests.store');
Route::get('/property-requests/{propertyRequest}', [\App\Http\Controllers\PropertyRequestController::class, 'show'])->name('property-requests.show');
Route::post('/property-requests/{propertyRequest}/offer', [\App\Http\Controllers\PropertyRequestOfferController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('property-requests.offer.store');

/*
|--------------------------------------------------------------------------
| تأمین مصالح — نیاز پروژه (سیمان، میلگرد، تهاتر) و غرفه‌های مصالح
|--------------------------------------------------------------------------
*/
Route::get('/materials', [\App\Http\Controllers\MaterialRequestController::class, 'index'])->name('materials.index');
Route::get('/materials/create', [\App\Http\Controllers\MaterialRequestController::class, 'create'])->name('materials.create');
Route::post('/materials', [\App\Http\Controllers\MaterialRequestController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('materials.store');
Route::get('/materials/{materialRequest}', [\App\Http\Controllers\MaterialRequestController::class, 'show'])->name('materials.show');
Route::post('/materials/{materialRequest}/offers', [\App\Http\Controllers\MaterialOfferController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('materials.offers.store');

// SEO
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

/*
|--------------------------------------------------------------------------
| پنل مشتری — سبد خرید، پرداخت زرین‌پال، پیگیری سفارش
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:customer'])->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'show'])->name('show');
    Route::post('/pay', [CheckoutController::class, 'pay'])->name('pay');
});

// این مسیر باید بدون middleware احراز هویت باشد چون زرین‌پال مستقیماً کاربر را به اینجا برمی‌گرداند
Route::get('/checkout/callback', [CheckoutController::class, 'callback'])->name('checkout.callback');

Route::middleware(['auth', 'role:customer'])->prefix('account')->name('customer.')->group(function () {
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/payment', [CustomerOrderController::class, 'submitPayment'])->name('orders.payment.submit');
});


Route::get('/clear-cache', function() {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');
    array_map('unlink', glob(storage_path('framework/sessions/*')));
    return 'Done! All caches and sessions cleared.';
});
/*
|--------------------------------------------------------------------------
| پنل غرفه‌دار
|--------------------------------------------------------------------------
*/
Route::get('/vendor/pending', fn () => view('vendor.pending'))->middleware('auth')->name('vendor.pending');

Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Vendor\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', VendorProductController::class)->except(['show', 'edit']);

    Route::get('/orders', [VendorOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{shipment}', [VendorOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{shipment}/ship', [VendorOrderController::class, 'markShipped'])->name('orders.ship');

    Route::get('/settlements', [VendorSettlementController::class, 'index'])->name('settlements.index');

    Route::get('/wallet', [\App\Http\Controllers\Vendor\WalletController::class, 'index'])->name('wallet.index');

    Route::get('/contract', [\App\Http\Controllers\Vendor\ContractController::class, 'index'])->name('contract.index');
    Route::get('/contract/{contract}', [\App\Http\Controllers\Vendor\ContractController::class, 'show'])->name('contract.show');
    Route::post('/contract/{contract}/sign', [\App\Http\Controllers\Vendor\ContractController::class, 'sign'])->name('contract.sign');
    Route::get('/contract/{contract}/print', [\App\Http\Controllers\Vendor\ContractController::class, 'print'])->name('contract.print');

    Route::get('/materials', [\App\Http\Controllers\Vendor\MaterialRequestController::class, 'index'])->name('materials.index');
});

/*
|--------------------------------------------------------------------------
| پنل زمین‌دار / ملک‌دار
|--------------------------------------------------------------------------
| برخلاف غرفه‌دار، حساب زمین‌دار نیازی به تأیید ادمین ندارد (بلافاصله بعد
| از تأیید شماره موبایل فعال است) — فقط هر آگهی ملک جداگانه در صف بررسی
| ادمین قرار می‌گیرد. بنابراین اینجا middleware انتظار تأییدی مثل vendor.pending
| لازم نیست.
*/
Route::middleware(['auth', 'role:landowner'])->prefix('landowner')->name('landowner.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Landowner\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/properties/create', [\App\Http\Controllers\Landowner\PropertyController::class, 'create'])->name('properties.create');
    Route::post('/properties', [\App\Http\Controllers\Landowner\PropertyController::class, 'store'])->name('properties.store');
    Route::get('/properties/{property}/edit', [\App\Http\Controllers\Landowner\PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('/properties/{property}', [\App\Http\Controllers\Landowner\PropertyController::class, 'update'])->name('properties.update');
    Route::delete('/properties/{property}', [\App\Http\Controllers\Landowner\PropertyController::class, 'destroy'])->name('properties.destroy');
});

/*
|--------------------------------------------------------------------------
| پنل ادمین
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/booths', [AdminBoothController::class, 'index'])->name('booths.index');
    Route::get('/booths/{booth}', [AdminBoothController::class, 'show'])->name('booths.show');
    Route::get('/booths/{booth}/document', [AdminBoothController::class, 'document'])->name('booths.document');
    Route::post('/booths/{booth}/status', [AdminBoothController::class, 'updateStatus'])->name('booths.status');
    Route::post('/booths/{booth}/approve', [AdminBoothController::class, 'approve'])->name('booths.approve');
    Route::post('/booths/{booth}/reject', [AdminBoothController::class, 'reject'])->name('booths.reject');
    Route::post('/booths/{booth}/suspend', [AdminBoothController::class, 'suspend'])->name('booths.suspend');
    Route::post('/booths/{booth}/commission', [AdminBoothController::class, 'updateCommission'])->name('booths.commission');
    Route::get('/products/videos', [\App\Http\Controllers\Admin\ProductController::class, 'videos'])->name('products.videos');
    Route::post('/products/{product}/video/{status}', [\App\Http\Controllers\Admin\ProductController::class, 'video'])->name('products.video');

    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->except(['show']);

    Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class)->except(['show']);
    Route::post('/announcements/{announcement}/toggle', [\App\Http\Controllers\Admin\AnnouncementController::class, 'toggle'])->name('announcements.toggle');

    Route::get('/settlements', [AdminSettlementController::class, 'index'])->name('settlements.index');
    Route::post('/settlements/generate', [AdminSettlementController::class, 'generate'])->name('settlements.generate');
    Route::post('/settlements/{settlement}/pay', [AdminSettlementController::class, 'markPaid'])->name('settlements.pay');

    // کیف پول و حسابداری غرفه‌ها
    Route::get('/wallets', [\App\Http\Controllers\Admin\WalletController::class, 'index'])->name('wallets.index');
    Route::get('/wallets/{booth}', [\App\Http\Controllers\Admin\WalletController::class, 'show'])->name('wallets.show');
    Route::post('/wallets/{booth}/adjust', [\App\Http\Controllers\Admin\WalletController::class, 'adjust'])->name('wallets.adjust');

    // قرارداد همکاری و سهم غرفه‌ها
    Route::get('/contracts', [\App\Http\Controllers\Admin\ContractController::class, 'index'])->name('contracts.index');
    Route::get('/contracts/{contract}', [\App\Http\Controllers\Admin\ContractController::class, 'show'])->name('contracts.show');
    Route::get('/contracts/{contract}/print', [\App\Http\Controllers\Admin\ContractController::class, 'print'])->name('contracts.print');
    Route::post('/booths/{booth}/contract', [\App\Http\Controllers\Admin\ContractController::class, 'issue'])->name('contracts.issue');
    Route::post('/contracts/{contract}/void', [\App\Http\Controllers\Admin\ContractController::class, 'void'])->name('contracts.void');

    // املاک — تأیید/رد آگهی و مشاهده درخواست‌های ارتباطی (فقط ادمین این درخواست‌ها را می‌بیند)
    Route::get('/properties', [\App\Http\Controllers\Admin\PropertyController::class, 'index'])->name('properties.index');
    Route::get('/properties/{property}', [\App\Http\Controllers\Admin\PropertyController::class, 'show'])->name('properties.show');
    Route::post('/properties/{property}/approve', [\App\Http\Controllers\Admin\PropertyController::class, 'approve'])->name('properties.approve');
    Route::post('/properties/{property}/reject', [\App\Http\Controllers\Admin\PropertyController::class, 'reject'])->name('properties.reject');
    Route::post('/properties/inquiries/{inquiry}/status', [\App\Http\Controllers\Admin\PropertyInquiryController::class, 'updateStatus'])->name('properties.inquiries.status');

    // آگهی‌های درخواست ملک — تأیید/رد و مشاهده‌ی پیشنهادهای رسیده
    Route::get('/property-requests', [\App\Http\Controllers\Admin\PropertyRequestController::class, 'index'])->name('property-requests.index');
    Route::get('/property-requests/{propertyRequest}', [\App\Http\Controllers\Admin\PropertyRequestController::class, 'show'])->name('property-requests.show');
    Route::post('/property-requests/{propertyRequest}/approve', [\App\Http\Controllers\Admin\PropertyRequestController::class, 'approve'])->name('property-requests.approve');
    Route::post('/property-requests/{propertyRequest}/reject', [\App\Http\Controllers\Admin\PropertyRequestController::class, 'reject'])->name('property-requests.reject');
    Route::post('/property-requests/offers/{offer}/status', [\App\Http\Controllers\Admin\PropertyRequestOfferController::class, 'updateStatus'])->name('property-requests.offers.status');

    Route::get('/materials', [\App\Http\Controllers\Admin\MaterialRequestController::class, 'index'])->name('materials.index');
    Route::get('/materials/{materialRequest}', [\App\Http\Controllers\Admin\MaterialRequestController::class, 'show'])->name('materials.show');
    Route::post('/materials/{materialRequest}/approve', [\App\Http\Controllers\Admin\MaterialRequestController::class, 'approve'])->name('materials.approve');
    Route::post('/materials/{materialRequest}/reject', [\App\Http\Controllers\Admin\MaterialRequestController::class, 'reject'])->name('materials.reject');
    Route::post('/materials/offers/{offer}/status', [\App\Http\Controllers\Admin\MaterialOfferController::class, 'updateStatus'])->name('materials.offers.status');

    // سفارش‌ها — بررسی و تأیید دستی پرداخت کارت‌به‌کارت
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/confirm-payment', [\App\Http\Controllers\Admin\OrderController::class, 'confirmPayment'])->name('orders.confirm-payment');
    Route::post('/orders/{order}/reject-payment', [\App\Http\Controllers\Admin\OrderController::class, 'rejectPayment'])->name('orders.reject-payment');
});

require __DIR__.'/auth.php'; // مسیرهای لاگین/ثبت‌نام (Breeze/Fortify)
<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController as AdminDestinationController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\HotelController as AdminHotelController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\Admin\TourController as AdminTourController;
use App\Http\Controllers\Admin\TransportOptionController;
use App\Http\Controllers\Admin\TravelStyleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\MapTileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TourismPageController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/map-tiles/{z}/{x}/{y}', MapTileController::class)
    ->whereNumber(['z', 'x', 'y'])
    ->name('map.tiles');

Route::redirect('/', '/fa');

Route::prefix('{locale}')
    ->whereIn('locale', array_keys(config('karako.locales')))
    ->middleware('locale')
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');
        Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
        Route::get('/tours/{slug}', [TourController::class, 'show'])->name('tours.show');
        Route::get('/destinations/{slug}', [DestinationController::class, 'show'])->name('destinations.show');
        Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/consultation', [ConsultationController::class, 'show'])->name('consultation');
        Route::post('/consultation', [ConsultationController::class, 'store'])->middleware('throttle:5,1')->name('consultation.store');
        Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
        Route::post('/feedback', [FeedbackController::class, 'store'])->middleware('throttle:5,1')->name('feedback.store');
        Route::get('/feedback/surveys/{survey}', [SurveyController::class, 'show'])->whereNumber('survey')->name('feedback.surveys.show');
        Route::post('/feedback/surveys/{survey}', [SurveyController::class, 'store'])->whereNumber('survey')->middleware('throttle:5,1')->name('feedback.surveys.store');
        Route::get('/info/{slug}', [PageController::class, 'show'])->name('pages.show');
        Route::get('/foundations', [TourismPageController::class, 'foundations'])->name('foundations');
        Route::get('/credits', [TourismPageController::class, 'credits'])->name('credits');
    });

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware(['auth', 'role:admin,editor'])->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/homepage', [HomepageController::class, 'edit'])->name('homepage.edit');
        Route::put('/homepage', [HomepageController::class, 'update'])->name('homepage.update');

        Route::get('/tours', [AdminTourController::class, 'index'])->name('tours.index');
        Route::get('/tours/create', [AdminTourController::class, 'create'])->name('tours.create');
        Route::post('/tours', [AdminTourController::class, 'store'])->name('tours.store');
        Route::get('/tours/{tour}', [AdminTourController::class, 'edit'])->name('tours.edit');
        Route::put('/tours/{tour}', [AdminTourController::class, 'update'])->name('tours.update');
        Route::delete('/tours/{tour}', [AdminTourController::class, 'destroy'])->name('tours.destroy');

        Route::get('/destinations', [AdminDestinationController::class, 'index'])->name('destinations.index');
        Route::get('/destinations/create', [AdminDestinationController::class, 'create'])->name('destinations.create');
        Route::post('/destinations', [AdminDestinationController::class, 'store'])->name('destinations.store');
        Route::get('/destinations/{destination}', [AdminDestinationController::class, 'edit'])->name('destinations.edit');
        Route::put('/destinations/{destination}', [AdminDestinationController::class, 'update'])->name('destinations.update');
        Route::delete('/destinations/{destination}', [AdminDestinationController::class, 'destroy'])->name('destinations.destroy');

        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/styles', [TravelStyleController::class, 'index'])->name('styles.index');
        Route::get('/styles/create', [TravelStyleController::class, 'create'])->name('styles.create');
        Route::post('/styles', [TravelStyleController::class, 'store'])->name('styles.store');
        Route::get('/styles/{style}', [TravelStyleController::class, 'edit'])->name('styles.edit');
        Route::put('/styles/{style}', [TravelStyleController::class, 'update'])->name('styles.update');
        Route::delete('/styles/{style}', [TravelStyleController::class, 'destroy'])->name('styles.destroy');

        Route::get('/transports', [TransportOptionController::class, 'index'])->name('transports.index');
        Route::get('/transports/create', [TransportOptionController::class, 'create'])->name('transports.create');
        Route::post('/transports', [TransportOptionController::class, 'store'])->name('transports.store');
        Route::get('/transports/{transport}', [TransportOptionController::class, 'edit'])->name('transports.edit');
        Route::put('/transports/{transport}', [TransportOptionController::class, 'update'])->name('transports.update');
        Route::delete('/transports/{transport}', [TransportOptionController::class, 'destroy'])->name('transports.destroy');

        Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
        Route::get('/services/create', [AdminServiceController::class, 'create'])->name('services.create');
        Route::post('/services', [AdminServiceController::class, 'store'])->name('services.store');
        Route::get('/services/{service}', [AdminServiceController::class, 'edit'])->name('services.edit');
        Route::put('/services/{service}', [AdminServiceController::class, 'update'])->name('services.update');
        Route::delete('/services/{service}', [AdminServiceController::class, 'destroy'])->name('services.destroy');

        Route::get('/hotels', [AdminHotelController::class, 'index'])->name('hotels.index');
        Route::get('/hotels/create', [AdminHotelController::class, 'create'])->name('hotels.create');
        Route::post('/hotels', [AdminHotelController::class, 'store'])->name('hotels.store');
        Route::get('/hotels/{hotel}', [AdminHotelController::class, 'edit'])->name('hotels.edit');
        Route::put('/hotels/{hotel}', [AdminHotelController::class, 'update'])->name('hotels.update');
        Route::delete('/hotels/{hotel}', [AdminHotelController::class, 'destroy'])->name('hotels.destroy');

        Route::get('/faqs', [AdminFaqController::class, 'index'])->name('faqs.index');
        Route::get('/faqs/create', [AdminFaqController::class, 'create'])->name('faqs.create');
        Route::post('/faqs', [AdminFaqController::class, 'store'])->name('faqs.store');
        Route::get('/faqs/{faq}', [AdminFaqController::class, 'edit'])->name('faqs.edit');
        Route::put('/faqs/{faq}', [AdminFaqController::class, 'update'])->name('faqs.update');
        Route::delete('/faqs/{faq}', [AdminFaqController::class, 'destroy'])->name('faqs.destroy');

        Route::get('/pages', [AdminPageController::class, 'index'])->name('pages.index');
        Route::get('/pages/create', [AdminPageController::class, 'create'])->name('pages.create');
        Route::post('/pages', [AdminPageController::class, 'store'])->name('pages.store');
        Route::get('/pages/{page}', [AdminPageController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{page}', [AdminPageController::class, 'destroy'])->name('pages.destroy');

        Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
        Route::get('/inquiries/{inquiry}', [InquiryController::class, 'show'])->name('inquiries.show');
        Route::put('/inquiries/{inquiry}', [InquiryController::class, 'update'])->name('inquiries.update');
        Route::delete('/inquiries/{inquiry}', [InquiryController::class, 'destroy'])->name('inquiries.destroy');

        Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/feedback/{submission}', [AdminFeedbackController::class, 'show'])->name('feedback.show');
        Route::put('/feedback/{submission}', [AdminFeedbackController::class, 'update'])->name('feedback.update');
        Route::delete('/feedback/{submission}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');

        Route::get('/surveys', [AdminSurveyController::class, 'index'])->name('surveys.index');
        Route::get('/surveys/create', [AdminSurveyController::class, 'create'])->name('surveys.create');
        Route::post('/surveys', [AdminSurveyController::class, 'store'])->name('surveys.store');
        Route::get('/surveys/{survey}/results', [AdminSurveyController::class, 'results'])->name('surveys.results');
        Route::get('/surveys/{survey}/export', [AdminSurveyController::class, 'export'])->name('surveys.export');
        Route::get('/surveys/{survey}', [AdminSurveyController::class, 'edit'])->name('surveys.edit');
        Route::put('/surveys/{survey}', [AdminSurveyController::class, 'update'])->name('surveys.update');
        Route::delete('/surveys/{survey}', [AdminSurveyController::class, 'destroy'])->name('surveys.destroy');

        Route::middleware('role:admin')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        });
    });
});

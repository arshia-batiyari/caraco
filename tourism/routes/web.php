<?php

use App\Http\Controllers\Admin\AttractionCategoryController;
use App\Http\Controllers\Admin\AttractionController as AdminAttractionController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController as AdminDestinationController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\HotelController as AdminHotelController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\MedicalCategoryController;
use App\Http\Controllers\Admin\MedicalDoctorController;
use App\Http\Controllers\Admin\MedicalFaqController;
use App\Http\Controllers\Admin\MedicalInquiryController;
use App\Http\Controllers\Admin\MedicalPageController;
use App\Http\Controllers\Admin\MedicalProviderController;
use App\Http\Controllers\Admin\MedicalServiceController;
use App\Http\Controllers\Admin\MedicalStepController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\Admin\TourController as AdminTourController;
use App\Http\Controllers\Admin\TransportOptionController;
use App\Http\Controllers\Admin\TravelStyleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttractionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MapTileController;
use App\Http\Controllers\MedicalController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TourismPageController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/map-tiles/{z}/{x}/{y}', MapTileController::class)
    ->whereNumber(['z', 'x', 'y'])
    ->name('map.tiles');

Route::redirect('/', '/fa', 301);

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
        Route::get('/medical', [MedicalController::class, 'index'])->name('medical.index');
        Route::get('/medical/services', [MedicalController::class, 'services'])->name('medical.services');
        Route::get('/medical/services/{slug}', [MedicalController::class, 'service'])->name('medical.services.show');
        Route::get('/medical/categories/{slug}', [MedicalController::class, 'category'])->name('medical.categories.show');
        Route::get('/medical/clinics/{slug}', [MedicalController::class, 'provider'])->name('medical.providers.show');
        Route::get('/medical/doctors/{slug}', [MedicalController::class, 'doctor'])->name('medical.doctors.show');
        Route::post('/medical/consultation', [MedicalController::class, 'store'])->middleware('throttle:5,1')->name('medical.store');
        Route::get('/attractions', [AttractionController::class, 'index'])->name('attractions.index');
        Route::get('/attractions/{slug}', [AttractionController::class, 'show'])->name('attractions.show');
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

        Route::get('/medical', [MedicalPageController::class, 'home'])->name('medical.home');
        Route::get('/medical/page', [MedicalPageController::class, 'edit'])->name('medical.page');
        Route::put('/medical/page', [MedicalPageController::class, 'update'])->name('medical.page.update');

        Route::get('/medical/categories', [MedicalCategoryController::class, 'index'])->name('medical.categories.index');
        Route::get('/medical/categories/create', [MedicalCategoryController::class, 'create'])->name('medical.categories.create');
        Route::post('/medical/categories', [MedicalCategoryController::class, 'store'])->name('medical.categories.store');
        Route::get('/medical/categories/{category}', [MedicalCategoryController::class, 'edit'])->name('medical.categories.edit');
        Route::put('/medical/categories/{category}', [MedicalCategoryController::class, 'update'])->name('medical.categories.update');
        Route::delete('/medical/categories/{category}', [MedicalCategoryController::class, 'destroy'])->name('medical.categories.destroy');

        Route::get('/medical/services', [MedicalServiceController::class, 'index'])->name('medical.services.index');
        Route::get('/medical/services/create', [MedicalServiceController::class, 'create'])->name('medical.services.create');
        Route::post('/medical/services', [MedicalServiceController::class, 'store'])->name('medical.services.store');
        Route::get('/medical/services/{service}', [MedicalServiceController::class, 'edit'])->name('medical.services.edit');
        Route::put('/medical/services/{service}', [MedicalServiceController::class, 'update'])->name('medical.services.update');
        Route::delete('/medical/services/{service}', [MedicalServiceController::class, 'destroy'])->name('medical.services.destroy');

        Route::get('/medical/clinics', [MedicalProviderController::class, 'index'])->name('medical.providers.index');
        Route::get('/medical/clinics/create', [MedicalProviderController::class, 'create'])->name('medical.providers.create');
        Route::post('/medical/clinics', [MedicalProviderController::class, 'store'])->name('medical.providers.store');
        Route::get('/medical/clinics/{provider}', [MedicalProviderController::class, 'edit'])->name('medical.providers.edit');
        Route::put('/medical/clinics/{provider}', [MedicalProviderController::class, 'update'])->name('medical.providers.update');
        Route::delete('/medical/clinics/{provider}', [MedicalProviderController::class, 'destroy'])->name('medical.providers.destroy');

        Route::get('/medical/doctors', [MedicalDoctorController::class, 'index'])->name('medical.doctors.index');
        Route::get('/medical/doctors/create', [MedicalDoctorController::class, 'create'])->name('medical.doctors.create');
        Route::post('/medical/doctors', [MedicalDoctorController::class, 'store'])->name('medical.doctors.store');
        Route::get('/medical/doctors/{doctor}', [MedicalDoctorController::class, 'edit'])->name('medical.doctors.edit');
        Route::put('/medical/doctors/{doctor}', [MedicalDoctorController::class, 'update'])->name('medical.doctors.update');
        Route::delete('/medical/doctors/{doctor}', [MedicalDoctorController::class, 'destroy'])->name('medical.doctors.destroy');

        Route::get('/medical/steps', [MedicalStepController::class, 'index'])->name('medical.steps.index');
        Route::get('/medical/steps/create', [MedicalStepController::class, 'create'])->name('medical.steps.create');
        Route::post('/medical/steps', [MedicalStepController::class, 'store'])->name('medical.steps.store');
        Route::get('/medical/steps/{step}', [MedicalStepController::class, 'edit'])->name('medical.steps.edit');
        Route::put('/medical/steps/{step}', [MedicalStepController::class, 'update'])->name('medical.steps.update');
        Route::delete('/medical/steps/{step}', [MedicalStepController::class, 'destroy'])->name('medical.steps.destroy');

        Route::get('/medical/faqs', [MedicalFaqController::class, 'index'])->name('medical.faqs.index');
        Route::get('/medical/faqs/create', [MedicalFaqController::class, 'create'])->name('medical.faqs.create');
        Route::post('/medical/faqs', [MedicalFaqController::class, 'store'])->name('medical.faqs.store');
        Route::get('/medical/faqs/{faq}', [MedicalFaqController::class, 'edit'])->name('medical.faqs.edit');
        Route::put('/medical/faqs/{faq}', [MedicalFaqController::class, 'update'])->name('medical.faqs.update');
        Route::delete('/medical/faqs/{faq}', [MedicalFaqController::class, 'destroy'])->name('medical.faqs.destroy');

        Route::get('/medical/inquiries', [MedicalInquiryController::class, 'index'])->name('medical.inquiries.index');
        Route::get('/medical/inquiries/{inquiry}', [MedicalInquiryController::class, 'show'])->name('medical.inquiries.show');
        Route::put('/medical/inquiries/{inquiry}', [MedicalInquiryController::class, 'update'])->name('medical.inquiries.update');
        Route::delete('/medical/inquiries/{inquiry}', [MedicalInquiryController::class, 'destroy'])->name('medical.inquiries.destroy');

        Route::get('/attractions', [AdminAttractionController::class, 'index'])->name('attractions.index');
        Route::get('/attractions/create', [AdminAttractionController::class, 'create'])->name('attractions.create');
        Route::post('/attractions', [AdminAttractionController::class, 'store'])->name('attractions.store');
        Route::get('/attractions/{attraction}', [AdminAttractionController::class, 'edit'])->name('attractions.edit');
        Route::put('/attractions/{attraction}', [AdminAttractionController::class, 'update'])->name('attractions.update');
        Route::delete('/attractions/{attraction}', [AdminAttractionController::class, 'destroy'])->name('attractions.destroy');

        Route::get('/attraction-categories', [AttractionCategoryController::class, 'index'])->name('attraction-categories.index');
        Route::get('/attraction-categories/create', [AttractionCategoryController::class, 'create'])->name('attraction-categories.create');
        Route::post('/attraction-categories', [AttractionCategoryController::class, 'store'])->name('attraction-categories.store');
        Route::get('/attraction-categories/{category}', [AttractionCategoryController::class, 'edit'])->name('attraction-categories.edit');
        Route::put('/attraction-categories/{category}', [AttractionCategoryController::class, 'update'])->name('attraction-categories.update');
        Route::delete('/attraction-categories/{category}', [AttractionCategoryController::class, 'destroy'])->name('attraction-categories.destroy');

        Route::middleware('role:admin')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        });
    });
});

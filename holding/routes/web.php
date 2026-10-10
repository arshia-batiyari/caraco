<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContactSettingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DivisionController as AdminDivisionController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\NavigationController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\HoldingPageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

Route::redirect('/', '/fa');

Route::prefix('{locale}')
    ->whereIn('locale', array_keys(config('karako.locales')))
    ->middleware('locale')
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');
        Route::get('/about', [PageController::class, 'about'])->name('about');
        Route::get('/divisions', [DivisionController::class, 'index'])->name('divisions.index');
        Route::get('/divisions/{slug}', [DivisionController::class, 'show'])->name('divisions.show');
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/info/{slug}', [PageController::class, 'show'])->name('pages.show');
        Route::get('/contact', [ContactController::class, 'show'])->name('contact');
        Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
        Route::get('/foundations', [HoldingPageController::class, 'foundations'])->name('foundations');
        Route::get('/credits', [HoldingPageController::class, 'credits'])->name('credits');
    });

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware(['auth', 'role:admin,editor'])->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/homepage', [HomepageController::class, 'edit'])->name('homepage.edit');
        Route::put('/homepage', [HomepageController::class, 'update'])->name('homepage.update');
        Route::get('/divisions', [AdminDivisionController::class, 'index'])->name('divisions.index');
        Route::put('/divisions', [AdminDivisionController::class, 'reorder'])->name('divisions.reorder');
        Route::get('/divisions/{division}', [AdminDivisionController::class, 'edit'])->name('divisions.edit');
        Route::put('/divisions/{division}', [AdminDivisionController::class, 'update'])->name('divisions.update');
        Route::get('/projects', [AdminProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create', [AdminProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [AdminProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}', [AdminProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{project}', [AdminProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [AdminProjectController::class, 'destroy'])->name('projects.destroy');
        Route::get('/pages', [AdminPageController::class, 'index'])->name('pages.index');
        Route::get('/pages/create', [AdminPageController::class, 'create'])->name('pages.create');
        Route::post('/pages', [AdminPageController::class, 'store'])->name('pages.store');
        Route::get('/pages/{page}', [AdminPageController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{page}', [AdminPageController::class, 'destroy'])->name('pages.destroy');
        Route::get('/navigation', [NavigationController::class, 'edit'])->name('navigation.edit');
        Route::put('/navigation', [NavigationController::class, 'update'])->name('navigation.update');
        Route::get('/contact', [ContactSettingsController::class, 'edit'])->name('contact.edit');
        Route::put('/contact', [ContactSettingsController::class, 'update'])->name('contact.update');
        Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
        Route::get('/inquiries/{inquiry}', [InquiryController::class, 'show'])->name('inquiries.show');

        Route::middleware('role:admin')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        });
    });
});

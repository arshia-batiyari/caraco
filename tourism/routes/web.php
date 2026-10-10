<?php

use App\Http\Controllers\TourismPageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/fa');

Route::prefix('{locale}')
    ->whereIn('locale', array_keys(config('karako.locales')))
    ->middleware('locale')
    ->group(function () {
        Route::get('/', [TourismPageController::class, 'home'])->name('home');
        Route::get('/journey', [TourismPageController::class, 'journey'])->name('journey');
        Route::get('/inquiry', [TourismPageController::class, 'inquiry'])->name('inquiry');
        Route::post('/inquiry', [TourismPageController::class, 'storeInquiry'])->name('inquiry.store');
        Route::get('/foundations', [TourismPageController::class, 'foundations'])->name('foundations');
        Route::get('/credits', [TourismPageController::class, 'credits'])->name('credits');
    });

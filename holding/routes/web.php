<?php

use App\Http\Controllers\HoldingPageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/fa');

Route::prefix('{locale}')
    ->whereIn('locale', array_keys(config('karako.locales')))
    ->middleware('locale')
    ->group(function () {
        Route::get('/', [HoldingPageController::class, 'home'])->name('home');
        Route::get('/divisions', [HoldingPageController::class, 'divisions'])->name('divisions');
        Route::get('/contact', [HoldingPageController::class, 'contact'])->name('contact');
        Route::post('/contact', [HoldingPageController::class, 'storeContact'])->name('contact.store');
        Route::get('/foundations', [HoldingPageController::class, 'foundations'])->name('foundations');
        Route::get('/credits', [HoldingPageController::class, 'credits'])->name('credits');
    });

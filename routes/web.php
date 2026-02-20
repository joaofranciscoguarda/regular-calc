<?php

use App\Http\Controllers\CalculationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CalculationController::class, 'index'])->name('home');
Route::post('/calculations', [CalculationController::class, 'store'])->name('calculations.store');
Route::delete('/calculations/{userHistory}', [CalculationController::class, 'destroy'])->name('calculations.destroy');
Route::delete('/calculations', [CalculationController::class, 'destroyAll'])->name('calculations.destroyAll');

Route::fallback(function () {
    return redirect()->route('home');
});

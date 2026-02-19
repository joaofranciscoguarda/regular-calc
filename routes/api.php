<?php

use App\Http\Controllers\CalculationController;
use Illuminate\Support\Facades\Route;

Route::get('/calculations', [CalculationController::class, 'index']);
Route::post('/calculations', [CalculationController::class, 'store']);
Route::delete('/calculations', [CalculationController::class, 'destroyAll']);
Route::delete('/calculations/{calculation}', [CalculationController::class, 'destroy']);

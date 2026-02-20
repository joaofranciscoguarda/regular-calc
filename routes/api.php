<?php

use App\Http\Controllers\Api\CalculationController;
use App\Http\Middleware\EnsureApiSession;
use Illuminate\Support\Facades\Route;

Route::middleware(EnsureApiSession::class)->group(function () {
    Route::get('/calculations', [CalculationController::class, 'index']);
    Route::post('/calculations', [CalculationController::class, 'store']);
    Route::delete('/calculations', [CalculationController::class, 'destroyAll']);
    Route::delete('/calculations/{userHistory}', [CalculationController::class, 'destroy']);
});

Route::get('up', function() {
    return response()->json("Healthy");
})->name('status');

Route::get("/", function() {
    return redirect()->route("status");
});

Route::fallback(function(){
    return redirect()->route("status");
});

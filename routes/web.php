<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StressController;

Route::get('/', function () {
    return redirect()->route('stress.index');
});

Route::get('/test', [StressController::class, 'index'])->name('stress.index');
Route::post('/test/start', [StressController::class, 'start'])->name('stress.start');

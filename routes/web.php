<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StressController;

Route::get('/', [StressController::class, 'landing'])->name('landing');
Route::get('/stress', [StressController::class, 'index'])->name('stress.index');
Route::get('/stats', [StressController::class, 'stats'])->name('stress.stats');
Route::post('/start', [StressController::class, 'start'])->name('stress.start');

// SQLI AUTOMATION ROUTES
Route::get('/sqli', [StressController::class, 'sqliIndex'])->name('sqli.index');
Route::post('/sqli/start', [StressController::class, 'sqliStart'])->name('sqli.start');

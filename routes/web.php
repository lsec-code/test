<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StressController;

Route::get('/', [StressController::class, 'index'])->name('stress.index');
Route::post('/login', [StressController::class, 'login'])->name('stress.login');
Route::post('/logout', [StressController::class, 'logout'])->name('stress.logout');
Route::get('/stats', [StressController::class, 'stats'])->name('stress.stats');
Route::get('/proxies/fetch', [StressController::class, 'fetchProxies'])->name('stress.proxies.fetch');
Route::post('/start', [StressController::class, 'start'])->name('stress.start');

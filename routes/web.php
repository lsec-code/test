<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StressController;

Route::get('/', [StressController::class, 'index'])->name('stress.index');
Route::post('/start', [StressController::class, 'start'])->name('stress.start');

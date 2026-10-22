<?php

use App\Http\Controllers\BenchmarkController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::get('/analisis', [BenchmarkController::class, 'index'])->name('analisis');
Route::post('/benchmark', [BenchmarkController::class, 'run'])->name('benchmark.run');
Route::view('/tentang', 'tentang')->name('tentang');
Route::view('/dashboard', 'dashboard')->name('dashboard');
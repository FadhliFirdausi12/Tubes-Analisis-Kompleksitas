<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::view('/analisis', 'analisis')->name('analisis');
Route::view('/tentang', 'tentang')->name('tentang');
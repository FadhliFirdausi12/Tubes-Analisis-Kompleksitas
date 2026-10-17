<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/tentang');
Route::view('/analisis', 'analisis')->name('analisis');
Route::view('/tentang', 'tentang')->name('tentang');
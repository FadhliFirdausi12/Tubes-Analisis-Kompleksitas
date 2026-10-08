<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/analisis');
Route::view('/analisis', 'analisis')->name('analisis');
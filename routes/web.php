<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\CalculatorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CalculatorController::class, 'index']);
Route::get('/admin', [AdminController::class, 'index']);

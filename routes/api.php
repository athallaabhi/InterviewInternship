<?php

use App\Http\Controllers\Api\CalculationController;
use App\Http\Controllers\Api\EmissionTypeController;
use App\Http\Controllers\Api\Admin;
use Illuminate\Support\Facades\Route;

// User-facing endpoints
Route::get('/emission-types', [EmissionTypeController::class, 'index']);
Route::get('/emission-types/{emissionType}/schema', [EmissionTypeController::class, 'schema']);
Route::post('/calculate', [CalculationController::class, 'calculate']);

// Admin endpoints
Route::prefix('admin')->group(function () {
    // Emission Types (read-only)
    Route::get('/emission-types', [Admin\EmissionTypeController::class, 'index']);

    // Categories (read-only)
    Route::get('/emission-types/{emissionType}/categories', [Admin\CategoryController::class, 'index']);

    // Category Values
    Route::get('/categories/{category}/values', [Admin\CategoryController::class, 'indexValues']);
    Route::post('/categories/{category}/values', [Admin\CategoryController::class, 'storeValue']);
    Route::put('/category-values/{categoryValue}', [Admin\CategoryController::class, 'updateValue']);
    Route::delete('/category-values/{categoryValue}', [Admin\CategoryController::class, 'destroyValue']);

    // Input Fields (read-only)
    Route::get('/emission-types/{emissionType}/input-fields', [Admin\InputFieldController::class, 'index']);

    // Coefficients (read-only)
    Route::get('/emission-types/{emissionType}/coefficients', [Admin\CoefficientController::class, 'index']);

    // Coefficient Values
    Route::get('/coefficients/{coefficient}/values', [Admin\CoefficientController::class, 'indexValues']);
    Route::post('/coefficients/{coefficient}/values', [Admin\CoefficientController::class, 'storeValue']);
    Route::put('/coefficient-values/{coefficientValue}', [Admin\CoefficientController::class, 'updateValue']);
    Route::delete('/coefficient-values/{coefficientValue}', [Admin\CoefficientController::class, 'destroyValue']);
});

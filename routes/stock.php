<?php

use App\Http\Controllers\StockImportController;
use Illuminate\Support\Facades\Route;

Route::get('/upload', [StockImportController::class, 'form']);
Route::post('/upload', [StockImportController::class, 'upload']);

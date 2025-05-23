<?php

use App\Http\Controllers\StockDataController;
use Illuminate\Support\Facades\Route;

Route::get('/stocks/{company}/changes', [StockDataController::class, 'intervals']);
Route::get('/stocks/{company}/range', [StockDataController::class, 'range']);

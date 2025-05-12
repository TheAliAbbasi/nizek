<?php

namespace App\Http\Controllers;

use App\Services\StockService;

class StockDataController extends Controller
{
    public function intervals(string $company, StockService $service)
    {
        return response()->json($service->analyzeIntervals($company));
    }
}

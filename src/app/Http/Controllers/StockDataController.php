<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockRangeRequest;
use App\Services\StockService;

class StockDataController extends Controller
{
    public function intervals(string $company, StockService $service)
    {
        return response()->json($service->analyzeIntervals($company));
    }

    public function range(string $company, StockRangeRequest $request, StockService $service)
    {
        return response()->json(
            $service->analyzeRange($company, $request->start, $request->end)
        );
    }
}

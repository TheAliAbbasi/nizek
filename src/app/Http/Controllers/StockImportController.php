<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessStockExcel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockImportController extends Controller
{
    public function index()
    {
        return Inertia::render('stock/Upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls',
        ]);

        $path = $request->file('excel')->store('stock_excels');
        ProcessStockExcel::dispatch($path);

        return back()->with('status', 'File uploaded. Processing started!');
    }
}

<?php

namespace App\Services;

use App\Models\StockPrice;
use Box\Spout\Reader\SheetInterface;

class StockService
{
    public function analyzeIntervals(string $company): array
    {
        $latest = StockPrice::forCompany($company)->orderByDesc('recorded_at')->first();
        $oldest = StockPrice::forCompany($company)->orderBy('recorded_at')->first();

        $intervals = collect(config('stock.intervals'))
            ->map(fn($date) => $date?->subDays(ceil($latest?->recorded_at->diffInDays(now()))));

        $results = [];

        /* @var \Carbon\Carbon $configDate */
        foreach ($intervals as $label => $configDate) {
            $startPrice = match ($label) {
                'MAX' => $oldest?->price,
                default => StockPrice::forCompany($company)
                    ->recordedAfterOrOn($configDate)
                    ->orderBy('recorded_at')
                    ->limit(1)
                    ->value('price'),
            };


            if ($startPrice === null || $latest === null) {
                $results[$label] = null;
                continue;
            }

            $change = $latest->price - $startPrice;

            $results[$label] = [
                'start' => round($startPrice, 2),
                'end' => round($latest->price, 2),
                'change' => round($change, 4),
                'percent' => $startPrice != 0 ? round((($latest->price / $startPrice)-1) * 100, 2) : null,
            ];
        }

        return [
            'company' => $company,
            'as_of' => $latest?->recorded_at,
            'intervals' => $results,
        ];
    }

    public function importFromSheet(SheetInterface $sheet, string $company, int $skipRows = 8, int $chunkSize = 500): void
    {
        $batch = [];
        $rowIndex = 0;

        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex++;
            if ($rowIndex <= $skipRows) continue;

            $cells = $row->toArray();
            $date = $cells[0] ?? null;
            $price = $cells[1] ?? null;

            if (!$date || !is_numeric($price)) continue;

            $batch[] = [
                'company' => $company,
                'recorded_at' => \Carbon\Carbon::parse($date),
                'price' => $price * 1E6,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $chunkSize) {
                StockPrice::insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            StockPrice::insert($batch);
        }
    }

}

<?php

namespace App\Services;

use App\Models\StockPrice;
use Box\Spout\Reader\SheetInterface;
use Carbon\Carbon;

class StockService
{
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
                'recorded_at' => Carbon::parse($date),
                'price' => $price * config('stock.price_scale'),
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

    public function analyzeIntervals(string $company): array
    {
        $latest = StockPrice::latestPriceOfCompany($company)->first();
        $oldest = StockPrice::oldestPriceOfCompany($company)->first();

        $intervals = collect(config('stock.intervals'))
            ->map(fn($date) => $date?->subDays(ceil($latest?->recorded_at->diffInDays(now()))));

        $results = [];

        foreach ($intervals as $label => $interval) {
            $startPrice = match ($label) {
                'MAX' => $oldest?->price,
                default => StockPrice::FirstPriceOfCompanyAfter($company, $interval)->value('price'),
            };

            $results[$label] = $this->calculateChange($startPrice, $latest?->price);
        }

        return [
            'company' => $company,
            'as_of' => $latest?->recorded_at,
            'intervals' => $results,
        ];
    }

    public function analyzeRange(string $company, string $start, string $end): array
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        $startPrice = StockPrice::FirstPriceOfCompanyAfter($company, $start)->value('price');
        $endPrice = StockPrice::FirstPriceOfCompanyAfter($company, $end)->value('price');

        return [
            'company' => $company,
            ...$this->calculateChange($startPrice, $endPrice),
        ];
    }

    private function calculateChange(?float $start, ?float $end): array
    {
        if ($start === null || $end === null) {
            return [
                'start' => $start,
                'end' => $end,
                'change' => null,
                'unit' => '%',
            ];
        }

        return [
            'start' => round($start, 2),
            'end' => round($end, 2),
            'change' => $start != 0
                ? round((($end / $start) - 1) * 100, 2)
                : null,
            'unit' => '%',
        ];
    }
}

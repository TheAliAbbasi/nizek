<?php

namespace App\Jobs;

use App\Models\StockPrice;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ProcessStockExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected int $chunkSize = 500;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle(): void
    {
        $fullPath = Storage::path($this->filePath);
        $reader = ReaderEntityFactory::createReaderFromFile($fullPath);
        $reader->open($fullPath);

        // Use only the "Dummy Data" sheet
        /* @var $sheet \Box\Spout\Reader\SheetInterface */
        $sheet = collect($reader->getSheetIterator())->first(
            fn($sheet) => $sheet->getName() === 'Dummy Data'
        );

        if (!$sheet) {
            $reader->close();
            throw new \Exception("Sheet 'Dummy Data' not found.");
        }

        $rowIndex = 0;
        $batch = [];

        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex++;

            // Skip top 8 rows (headers)
            if ($rowIndex <= 8) {
                continue;
            }

            $cells = $row->toArray();
            $date = $cells[0] ?? null;
            $price = $cells[1] ?? null;

            if (!$date || !is_numeric($price)) continue;

            $batch[] = [
                'company' => 'AAPL',
                'recorded_at' => Carbon::parse($date),
                'price' => $price,
            ];

            if (count($batch) >= $this->chunkSize) {
                StockPrice::insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            StockPrice::insert($batch);
        }

        $reader->close();
    }
}

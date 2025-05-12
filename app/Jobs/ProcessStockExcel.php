<?php

namespace App\Jobs;

use App\Services\StockService;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessStockExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected int $chunkSize;

    public function __construct(string $filePath, int $chunkSize = 500)
    {
        $this->filePath = $filePath;
        $this->chunkSize = $chunkSize;
    }

    public function handle(StockService $stockService): void
    {
        $fullPath = Storage::path($this->filePath);
        $reader = ReaderEntityFactory::createReaderFromFile($fullPath);
        $reader->open($fullPath);

        $sheet = collect($reader->getSheetIterator())->first(
            fn($sheet) => $sheet->getName() === 'Dummy Data'
        );

        if (!$sheet) {
            $reader->close();
            throw new \Exception("Sheet 'Dummy Data' not found.");
        }

        $stockService->importFromSheet($sheet, company: 'AAPL', skipRows: 6, chunkSize: $this->chunkSize);

        $reader->close();
    }
}

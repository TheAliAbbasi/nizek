<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class StockPrice extends Model
{
    protected $fillable = ['company', 'recorded_at', 'price'];
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime:Y-m-d',
        ];
    }

    // Scope for filtering by company
    public function scopeForCompany($query, string $company)
    {
        return $query->where('company', $company);
    }

    // Scope for date filtering (optional, for reuse)
    public function scopeRecordedAfterOrOn($query, string|Carbon $date)
    {
        return $query->where('recorded_at', '>=', $date);
    }

    public function scopeLatestPriceOfCompany($query, string $company)
    {
        return $query->forCompany($company)
            ->orderByDesc('recorded_at')
            ->limit(1);
    }

    public function scopeOldestPriceOfCompany($query, string $company)
    {
        return $query->forCompany($company)
            ->orderBy('recorded_at')
            ->limit(1);
    }

    public function scopeFirstPriceOfCompanyAfter($query, string $company, string|Carbon $date)
    {
        return $query->forCompany($company)
            ->recordedAfterOrOn($date)
            ->orderBy('recorded_at')
            ->limit(1);
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $value / config('stock.price_scale'),
            set: fn(string $value) => intval($value * config('stock.price_scale')),
        );

    }

}

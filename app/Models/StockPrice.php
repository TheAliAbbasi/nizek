<?php

namespace App\Models;

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
    public function scopeRecordedAfterOrOn($query, string|\Carbon\Carbon $date)
    {
        return $query->where('recorded_at', '>=', $date);
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $value / 1E6,
            set: fn(string $value) => intval($value * 1E6),
        );

    }

}

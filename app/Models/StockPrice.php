<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockPrice extends Model
{
    protected $fillable = ['company', 'recorded_at', 'price'];
    public $timestamps = true;
}

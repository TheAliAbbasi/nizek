<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockRangeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start' => ['required', 'date'],
            'end'   => ['required', 'date', 'after_or_equal:start'],
        ];
    }

    public function authorize(): bool
    {
        return true; // adjust if you want auth checks later
    }
}

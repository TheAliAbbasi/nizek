<?php

return [
    'intervals' => [
        '1D'   => now()->subDay(),
        '1M'   => now()->subMonth(),
        '3M'   => now()->subMonths(3),
        '6M'   => now()->subMonths(6),
        'YTD'  => now()->startOfYear(),
        '1Y'   => now()->subYear(),
        '3Y'   => now()->subYears(3),
        '5Y'   => now()->subYears(5),
        '10Y'  => now()->subYears(10),
        'MAX'  => null,
    ]
];

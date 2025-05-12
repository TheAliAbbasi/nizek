# 📊 Laravel Stock Price Analysis API

This project provides APIs to import, store, and analyze historical stock prices for various companies. It supports:

    Importing Excel stock data via streaming (Spout)

    Analyzing value/percent change over fixed intervals (1D, 1M, 3Y, etc.)

    Calculating custom range comparisons (start and end)

    Clean, performant Laravel architecture with scoped queries and service separation

## 🚀 Features

    ✅ Stream-based Excel import with chunked inserts

    ✅ Predefined financial intervals (1D, 1M, YTD, MAX, etc.)

    ✅ Custom range comparison via query parameters

    ✅ Efficient queries using indexed date lookups

    ✅ Configurable price scaling (e.g. micro-units)

## 📦 Requirements

    PHP 8.1+

    Laravel 10+

    PostgreSQL (or any supported DB)

    Composer

    Docker/Sail (recommended)

## ⚙️ Configuration

    Copy .env.example → .env

    Set your DB credentials

    Optionally, adjust price scaling:
```php
// config/stock.php
'price_scale' => 1_000_000,
```


## 🗂️ API Endpoints
### 📥 Import Stock Data (via Excel)

#### Route: `POST /upload`

Uploads an Excel file for a given company.
(Sheet name must be "Dummy Data"; first 8 rows are skipped.)

    Uses App\Services\StockService::importFromSheet() internally.

### 📈 Interval-Based Price Change

#### Route: `GET /api/stocks/{company}/changes`

Returns price and percentage changes for configured intervals.

Example Response:
```json
{
  "company": "AAPL",
  "as_of": "2025-04-30T00:00:00.000000Z",
  "intervals": {
    "1D": {
      "start": 164.77,
      "end": 161.84,
      "change": -1.78,
      "unit": "%"
    },
    "1M": {
      "start": 151.49,
      "end": 161.84,
      "change": 6.83,
      "unit": "%"
    },
    "3M": {
      "start": 156.69,
      "end": 161.84,
      "change": 3.29,
      "unit": "%"
    },
    "6M": {
      "start": 126.11,
      "end": 161.84,
      "change": 28.33,
      "unit": "%"
    },
    "YTD": {
      "start": 145.64,
      "end": 161.84,
      "change": 11.12,
      "unit": "%"
    },
    "1Y": {
      "start": 123.75,
      "end": 161.84,
      "change": 30.78,
      "unit": "%"
    },
    "3Y": {
      "start": 40.99,
      "end": 161.84,
      "change": 294.88,
      "unit": "%"
    },
    "5Y": {
      "start": 29.26,
      "end": 161.84,
      "change": 453.02,
      "unit": "%"
    },
    "10Y": {
      "start": 14.77,
      "end": 161.84,
      "change": 996.05,
      "unit": "%"
    },
    "MAX": {
      "start": 6.34,
      "end": 161.84,
      "change": 2450.96,
      "unit": "%"
    }
  }
}
```

### 🧮 Custom Range Comparison

#### Route: `GET /api/stocks/{company}/range?start=YYYY-MM-DD&end=YYYY-MM-DD`

Returns price change and percent between any two dates.

Example Response:
```json
{
  "company": "AAPL",
  "start": 151.49,
  "end": 161.84,
  "change": 6.83,
  "unit": "%"
}
```

## 📦 Installation

1. Clone the repository with `git clone git@github.com:TheAliAbbasi/nizek.git`
2. Copy .env file from example: `cp src/.env.example src/.env`
3. Build docker image with `docker compose build app`
4. Run docker compose: `docker compose up -d`
5. generate app key: `php artisan key:generate`
6. Run migrations: `docker compose exec app php artisan migrate`
7. Visit http://localhost/upload in your browser
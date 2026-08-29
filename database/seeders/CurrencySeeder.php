<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['name' => 'US Dollar', 'symbol' => '$', 'iso_code' => 'USD'],
            ['name' => 'British Pound', 'symbol' => '£', 'iso_code' => 'GBP'],
            ['name' => 'Australian Dollar', 'symbol' => 'A$', 'iso_code' => 'AUD'],
            ['name' => 'Euro', 'symbol' => '€', 'iso_code' => 'EUR'],
            ['name' => 'Canadian Dollar', 'symbol' => 'C$', 'iso_code' => 'CAD'],
            ['name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'iso_code' => 'NZD'],
            ['name' => 'Indian Rupee', 'symbol' => '₹', 'iso_code' => 'INR'],
            ['name' => 'UAE Dirham', 'symbol' => 'د.إ', 'iso_code' => 'AED'],
            ['name' => 'Saudi Riyal', 'symbol' => '﷼', 'iso_code' => 'SAR'],
            ['name' => 'Singapore Dollar', 'symbol' => 'S$', 'iso_code' => 'SGD'],
            ['name' => 'South African Rand', 'symbol' => 'R', 'iso_code' => 'ZAR'],
            ['name' => 'Japanese Yen', 'symbol' => '¥', 'iso_code' => 'JPY'],
            ['name' => 'Swiss Franc', 'symbol' => 'CHF', 'iso_code' => 'CHF'],
            ['name' => 'Mexican Peso', 'symbol' => 'MX$', 'iso_code' => 'MXN'],
            ['name' => 'Brazilian Real', 'symbol' => 'R$', 'iso_code' => 'BRL'],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(['iso_code' => $currency['iso_code']], $currency);
        }
    }
}

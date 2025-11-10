<?php

namespace Database\Seeders\Production;

use App\Models\Wallet\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::firstOrCreate([
            'code' => 'USD',
            'short_code' => '$'
        ], [
            'id' => 1,
            'name' => 'Dollar',
            'minor_unit' => 100,
            'decimal_places' =>  2
        ]);

        Currency::firstOrCreate([
            'code' => 'KHR',
            'short_code' => '៛'
        ], [
            'id' => 2,
            'name' => 'Riel',
            'minor_unit' => 100,
            'decimal_places' =>  2
        ]);
    }
}

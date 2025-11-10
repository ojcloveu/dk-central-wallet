<?php

namespace Database\Seeders\Production;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * php artisan db:seed --class="Database\\Seeders\\Production\\BaseSeeder"
     */
    public function run(): void
    {
        $path = 'Database\\Seeders\\Production\\';
        $this->call($path . 'CurrencySeeder');
        // currency must above user as after user create need to generate default wallet
        $this->call($path . 'UserSeeder');
    }
}

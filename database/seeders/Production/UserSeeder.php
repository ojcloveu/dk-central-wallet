<?php

namespace Database\Seeders\Production;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $model = User::class;

        $model::firstOrCreate(['email' => 'dev@dev.com'], [
            'name' => 'dev',
            'password' => Hash::make(1234)
        ]);
    }
}

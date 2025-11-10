<?php

use App\Enum\Triggers\CurrencyTgEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        CurrencyTgEnum::addTrigger();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        CurrencyTgEnum::clearTrigger();
    }
};

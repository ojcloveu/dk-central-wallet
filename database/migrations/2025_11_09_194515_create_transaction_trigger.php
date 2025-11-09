<?php

use Illuminate\Support\Facades\Schema;
use App\Enum\Triggers\TransactionTgEnum;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        TransactionTgEnum::addTrigger();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        TransactionTgEnum::clearTrigger();
    }
};

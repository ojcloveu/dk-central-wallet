<?php

use App\Enum\Triggers\WalletTgEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        WalletTgEnum::addTrigger();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        WalletTgEnum::clearTrigger();
    }
};

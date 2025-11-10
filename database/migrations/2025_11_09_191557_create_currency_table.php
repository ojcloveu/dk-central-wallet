<?php

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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8); // e.g. USD
            $table->string('short_code', 8); // e.g. USD
            $table->string('name', 64);
            $table->unsignedSmallInteger('minor_unit')->default(100); // e.g. 100 for cents, 1000 for mills
            $table->unsignedSmallInteger('decimal_places')->default(2); // helpful metadata
            $table->timestamps();
            $table->unique(['code', 'short_code']);
            $table->unsignedBigInteger('create_by')->nullable();
            $table->unsignedBigInteger('update_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};

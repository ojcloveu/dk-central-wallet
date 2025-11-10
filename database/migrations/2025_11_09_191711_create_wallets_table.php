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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('currency_id')->index();

            // store minor units as DECIMAL(64,0) per request; we'll use integer semantics.
            $table->bigInteger('balance')->default(0); // can be negative

            $table->string('name')->nullable();
            $table->string('type', 36)->default('default'); // no enum

            $table->timestamps();
            $table->unsignedBigInteger('create_by')->nullable();
            $table->unsignedBigInteger('update_by')->nullable();
            $table->json('meta')->nullable(); // for read-only, write by currency table trigger, for performance reason

            // DB integrity
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('currency_id')->references('id')->on('currencies');

            // indexes used for lookups and reports
            $table->index(['user_id', 'currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};

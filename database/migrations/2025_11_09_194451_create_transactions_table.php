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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // wallet relationship
            $table->unsignedBigInteger('wallet_id')->index();

            $table->bigInteger('balance')->default(0);

            // status string: withdraw, deposit, transfer
            $table->string('status', 36)->index();


            // confirmed boolean
            $table->boolean('confirmed')->default(false);


            // reason / note
            $table->string('remark', 255)->nullable();


            // transfer grouping: both sides share group id
            $table->uuid('transfer_group_id')->nullable()->index();

            // morphs
            $table->string('actionable_type')->nullable();
            $table->unsignedBigInteger('actionable_id')->nullable();
            $table->string('holder_type')->nullable();
            $table->unsignedBigInteger('holder_id')->nullable();


            $table->json('meta')->nullable();


            $table->timestamps();
            $table->unsignedBigInteger('create_by')->nullable();
            $table->unsignedBigInteger('update_by')->nullable();


            // constraints
            $table->foreign('wallet_id')->references('id')->on('wallets');


            // critical indexes for reporting / query speed
            $table->index(['wallet_id', 'created_at']);
            $table->index(['holder_type', 'holder_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

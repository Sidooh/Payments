<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('payments', function(Blueprint $table) {
            $table->id();

            $table->decimal("amount");
            $table->string("status", 15); // PENDING or COMPLETED
            $table->string("type", 15); // ['MOBILE', 'SIDOOH', 'BANK', 'PAYPAL', 'OTHER'] payment methods?
            $table->string("subtype", 15); // 'STK', 'C2B', 'CBA', 'WALLET', 'BONUS'
            $table->unsignedBigInteger("provider_id");
            $table->string("description")->nullable();
            $table->string('reference')->nullable(); // external party reference

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

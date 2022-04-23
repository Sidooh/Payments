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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();

            $table->string('type', 20);
            $table->integer('balance')->unsigned()->default(0);
            $table->foreignId('account_id')->unsigned();
            $table->foreignId('enterprise_id')->nullable();

            $table->unique(['type', 'account_id', 'enterprise_id']);

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
        Schema::dropIfExists('vouchers');
    }
};

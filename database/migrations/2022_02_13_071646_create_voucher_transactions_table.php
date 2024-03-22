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
    public function up()
    {
        Schema::create('voucher_transactions', function(Blueprint $table) {
            $table->id();

            $table->string('type', 20); // DEBIT or CREDIT
            $table->integer('amount')->unsigned();
            $table->string('description');
            $table->foreignId('voucher_id')->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voucher_transactions');
    }
};

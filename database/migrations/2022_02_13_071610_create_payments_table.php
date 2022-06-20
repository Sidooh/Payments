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
        Schema::create('payments', function(Blueprint $table) {
            $table->id();

            $table->morphs('payable');
            $table->decimal('amount');
            $table->string('details');
            $table->string('status', 15); // PENDING or COMPLETED
            $table->string('type', 15); // ['MOBILE', 'SIDOOH', 'BANK', 'PAYPAL', 'OTHER'] payment methods?
            $table->string('subtype', 15); // 'STK', 'C2B', 'CBA', 'WALLET', 'BONUS'
            $table->morphs('provider');

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
        Schema::dropIfExists('payments');
    }
};

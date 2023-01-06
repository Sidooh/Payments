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
        Schema::table('payments', function(Blueprint $table) {
            //
            $table->unsignedBigInteger('account_id')->nullable();

            $table->string('destination_type', 15)->nullable(); // ['MOBILE', 'SIDOOH', 'BANK', 'PAYPAL', 'OTHER'] payment methods?
            $table->string('destination_subtype', 15)->nullable(); // 'STK', 'C2B', 'CBA', 'WALLET', 'BONUS'
            $table->unsignedBigInteger('destination_provider_id')->nullable();
            $table->json('destination_data')->nullable();
            $table->string('ipn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function(Blueprint $table) {
            //
            $table->dropColumn([
                'ipn', 'destination_data', 'destination_provider_id', 'destination_subtype', 'destination_type', 'account_id',
            ]);
        });
    }
};

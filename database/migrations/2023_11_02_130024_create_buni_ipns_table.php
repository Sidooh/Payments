<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('buni_ipns', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_reference')->index();
            $table->string('request_id')->index();
            $table->string('channel_code')->nullable();
            $table->string('timestamp');
            $table->decimal('transaction_amount');
            $table->string('currency');
            $table->string('customer_reference');
            $table->string('customer_name');
            $table->string('customer_mobile_number')->nullable();
            $table->decimal('balance')->nullable();
            $table->string('narration');
            $table->string('credit_account_identifier');
            $table->string('organization_short_code');
            $table->string('till_number')->nullable();

            $table->string('status')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('buni_ipns');
    }
};

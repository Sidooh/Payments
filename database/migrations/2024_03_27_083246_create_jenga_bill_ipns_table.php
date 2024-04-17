<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('jenga_bill_ipns', function (Blueprint $table) {
            $table->id();

            $table->string('bill_number')->index();
            $table->decimal('bill_amount');
            $table->string('customer_ref_number');
            $table->string('bankreference');
            $table->string('tran_particular')->nullable();
            $table->string('payment_mode');
            $table->string('transaction_date');
            $table->string('phonenumber')->nullable();
            $table->string('debitaccount');
            $table->string('debitcustname')->nullable();
            $table->string('currency')->nullable();

            $table->timestamps();
        });
    }
};

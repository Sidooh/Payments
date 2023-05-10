<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('buni_stk_callbacks', function (Blueprint $table) {
            $table->id();

            $table->string('merchant_request_id')->index();
            $table->string('checkout_request_id')->index();
            $table->integer('result_code');
            $table->string('result_desc');
            $table->decimal('amount')->nullable();
            $table->string('mpesa_receipt_number')->nullable();
            $table->decimal('balance')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('transaction_date')->nullable();

            $table->timestamps();
            $table->foreign('checkout_request_id')
                ->references('checkout_request_id')
                ->on('buni_stk_requests')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('merchant_request_id')
                ->references('merchant_request_id')
                ->on('buni_stk_requests')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};

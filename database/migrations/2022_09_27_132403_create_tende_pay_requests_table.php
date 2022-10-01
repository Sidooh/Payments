<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tende_pay_requests', function (Blueprint $table) {
            $table->id();

            // add fields
            $table->string('service');
            $table->string('unique_reference'); // Can be used to verify values in DB
            $table->string('transaction_reference')->unique();
            $table->json('text');
            $table->string('msisdn')->nullable();
            $table->string('timestamp');

            $table->string('response_code');
            $table->string('response_message');
            $table->string('successful')->nullable();
            $table->string('status');

            $table->timestamps();

            $table->string('relation_id')->nullable();
        });
    }
};

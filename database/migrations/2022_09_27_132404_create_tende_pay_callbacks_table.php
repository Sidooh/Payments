<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tende_pay_callbacks', function (Blueprint $table) {
            $table->id();

            // add fields
            $table->string('initiator_reference')->unique();
            $table->string('response_code');
            $table->string('status');
            $table->string('status_description');

            $table->double('amount');
            $table->string('account_reference');
            $table->string('confirmation_code');
            $table->string('msisdn');
            $table->string('receiver_party_name')->nullable();
            $table->string('date');

            $table->timestamps();

            $table->foreign('initiator_reference')
                ->references('transaction_reference')
                ->on('tende_pay_requests')->onDelete('cascade')->onUpdate('cascade');
        });
    }
};

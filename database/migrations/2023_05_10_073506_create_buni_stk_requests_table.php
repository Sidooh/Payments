<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('buni_stk_requests', function (Blueprint $table) {
            $table->id();

            $table->string('phone_number', 15);
            $table->decimal('amount');
            $table->string('invoice_number');
            $table->boolean('shared_short_code')->default(true);
            $table->integer('org_short_code')->nullable();
            $table->string('org_pass_key')->nullable();
            $table->string('description');

            $table->string('status')->default('REQUESTED');
            $table->string('merchant_request_id')->unique();
            $table->string('checkout_request_id')->index();
            $table->unsignedInteger('relation_id')->nullable();
            $table->timestamps();
        });
    }
};

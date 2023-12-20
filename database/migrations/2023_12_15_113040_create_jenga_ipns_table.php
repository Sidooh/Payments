<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('jenga_ipns', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_reference')->index();
            $table->string('transaction_date');
            $table->string('transaction_payment_mode');
            $table->decimal('transaction_amount');
            $table->string('transaction_bill_number');
            $table->string('transaction_served_by');
            $table->string('transaction_additional_info');
            $table->decimal('transaction_order_amount');
            $table->decimal('transaction_service_charge');
            $table->string('transaction_status');
            $table->string('transaction_remarks');

            $table->string('customer_name');
            $table->string('customer_mobile_number');
            $table->string('customer_reference');

            $table->string('bank_reference');
            $table->string('bank_transaction_type');
            $table->string('bank_account')->nullable();

            $table->timestamps();
        });
    }
};

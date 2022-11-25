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
        Schema::create('voucher_types', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->boolean('is_locked')->default(false);
            $table->unsignedInteger('limit_amount')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->json('settings')->nullable();

            $table->foreignId('account_id');

            $table->unique(['name', 'account_id']);

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
        Schema::dropIfExists('voucher_types');
    }
};

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
    public function up(): void
    {
        Schema::table('float_account_transactions', function(Blueprint $table) {
            $table->decimal('balance', 10)->default(0)->after('amount');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('float_account_transactions', function(Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
};

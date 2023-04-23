<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
            $table->string('provider', 30)->storedAs('CONCAT(type, subtype)');
            $table->string('destination_provider', 30)
                ->storedAs('CONCAT(destination_type, destination_subtype)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
            $table->dropColumn(['provider', 'destination_provider']);
        });
    }
};

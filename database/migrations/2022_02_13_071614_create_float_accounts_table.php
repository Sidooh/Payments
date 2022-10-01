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
        Schema::create('float_accounts', function(Blueprint $table) {
            $table->id();

            $table->decimal('balance', 10)->default(0);
            $table->morphs('floatable');

            $table->unique(['floatable_id', 'floatable_type']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('float_accounts');
    }
};

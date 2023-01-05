<?php

use App\Models\VoucherType;
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
        Schema::table('vouchers', function(Blueprint $table) {
            $table->dropColumn('type');

            $table->foreignIdFor(VoucherType::class);

            $table->unique(['voucher_type_id', 'account_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('vouchers', function(Blueprint $table) {
            $table->dropUnique(['voucher_type_id', 'account_id']);
            $table->dropForeignIdFor(VoucherType::class);
        });
    }
};

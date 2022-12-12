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
    public function up()
    {
        Schema::table('vouchers', function(Blueprint $table) {
            //
            $table->string('type')->change()->nullable();

            $table->foreignIdFor(VoucherType::class);

            $table->unique(['voucher_type_id', 'account_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vouchers', function(Blueprint $table) {
            //
            $table->dropUnique(['voucher_type_id', 'account_id']);
            $table->dropForeignIdFor(VoucherType::class);
        });
    }
};

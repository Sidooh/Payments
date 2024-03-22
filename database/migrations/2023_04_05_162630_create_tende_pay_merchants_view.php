<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE VIEW tende_pay_merchants_view
            AS
                SELECT DISTINCT SUBSTRING_INDEX(receiver_party_name, ' - ', 1)  AS code,
                                SUBSTRING_INDEX(receiver_party_name, ' - ', -1) AS name,
                                receiver_party_name
                from tende_pay_callbacks;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS tende_pay_merchants_view;");
    }
};

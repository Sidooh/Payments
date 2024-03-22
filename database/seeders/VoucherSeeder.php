<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Voucher::create([
            'balance'         => 5000,
            'account_id'      => 2,
            'voucher_type_id' => 1,
        ]);

        Voucher::create([
            'balance'         => 5000,
            'account_id'      => 1,
            'voucher_type_id' => 1,
        ]);
    }
}

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
            //            'type'            => VoucherType::SIDOOH,
            'balance'         => 5000,
            'account_id'      => 6,
            'voucher_type_id' => 1,
        ]);

        Voucher::create([
            //            'type'            => VoucherType::SIDOOH,
            'balance'         => 5000,
            'account_id'      => 1,
            'voucher_type_id' => 1,
        ]);

        /** Production
         */
        /*Voucher::create([
            'type'       => VoucherType::SIDOOH,
            'balance'    => 5000,
            'account_id' => 7,
        ]);*/
    }
}

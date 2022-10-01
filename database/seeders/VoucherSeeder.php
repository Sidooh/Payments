<?php

namespace Database\Seeders;

use App\Enums\VoucherType;
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
            'type'       => VoucherType::SIDOOH,
            'balance'    => 5000,
            'account_id' => 46,
        ]);

        Voucher::create([
            'type'       => VoucherType::SIDOOH,
            'balance'    => 5000,
            'account_id' => 12,
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

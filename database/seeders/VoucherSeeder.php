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
    public function run()
    {
        Voucher::create([
            'type'       => VoucherType::SIDOOH,
            'balance'    => 5000,
            'account_id' => 46,
        ]);
    }
}

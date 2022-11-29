<?php

namespace Database\Seeders;

use App\Models\VoucherType;
use Illuminate\Database\Seeder;

class VoucherTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        VoucherType::create([
            'name'         => 'SIDOOH',
            'limit_amount' => 70000,
            'account_id'   => 1,
        ]);
    }
}

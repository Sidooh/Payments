<?php

namespace Database\Seeders;

use App\Models\FloatAccount;
use Illuminate\Database\Seeder;

class FloatAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        FloatAccount::create([
            'account_id'     => 1,
            'floatable_id'   => 1,
            'floatable_type' => 'ENTERPRISE',
        ]);
    }
}

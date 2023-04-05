<?php

namespace Database\Seeders;

use App\Models\FloatAccount;
use Illuminate\Database\Seeder;

class FloatAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FloatAccount::create([
            'account_id'     => 1,
            'floatable_id'   => 1,
            'floatable_type' => 'ENTERPRISE',
        ]);
        FloatAccount::create([
            'account_id'     => 2,
            'floatable_id'   => 2,
            'floatable_type' => 'ENTERPRISE',
        ]);
    }
}

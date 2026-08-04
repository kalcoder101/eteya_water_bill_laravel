<?php

namespace Database\Seeders;

use App\Models\WorkingPeriod;
use Illuminate\Database\Seeder;

class WorkingPeriodSeeder extends Seeder
{
    public function run(): void
    {
        WorkingPeriod::create([
            'work_year'  => '2017',
            'work_month' => 'Fulbaana',
            'is_active'  => true,
        ]);
    }
}

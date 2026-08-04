<?php

namespace Database\Seeders;

use App\Models\SeasonalConsumption;
use Illuminate\Database\Seeder;

class SeasonalConsumptionSeeder extends Seeder
{
    public function run(): void
    {
        $readings = [
            ['meter_reading_id' => 'RD-001', 'meter_serial' => 'ETY-0001', 'reading_date' => '2024-09-30', 'current_reading' => 110.00,  'collector' => 'Eyasu', 'reading_year' => '2017', 'reading_month' => 'Fulbaana', 'sync_status' => 'Synced', 'reading_branch' => 'Eteya'],
            ['meter_reading_id' => 'RD-002', 'meter_serial' => 'ETY-0002', 'reading_date' => '2024-09-30', 'current_reading' => 65.00,   'collector' => 'Eyasu', 'reading_year' => '2017', 'reading_month' => 'Fulbaana', 'sync_status' => 'Synced', 'reading_branch' => 'Eteya'],
            ['meter_reading_id' => 'RD-003', 'meter_serial' => 'ETY-0003', 'reading_date' => '2024-09-30', 'current_reading' => 215.00,  'collector' => 'Eyasu', 'reading_year' => '2017', 'reading_month' => 'Fulbaana', 'sync_status' => 'Synced', 'reading_branch' => 'Eteya'],
            ['meter_reading_id' => 'RD-004', 'meter_serial' => 'ETY-0004', 'reading_date' => '2024-09-30', 'current_reading' => 12.00,   'collector' => 'Eyasu', 'reading_year' => '2017', 'reading_month' => 'Fulbaana', 'sync_status' => 'Synced', 'reading_branch' => 'Eteya'],
            ['meter_reading_id' => 'RD-005', 'meter_serial' => 'ETY-0005', 'reading_date' => '2024-09-30', 'current_reading' => 505.00,  'collector' => 'Eyasu', 'reading_year' => '2017', 'reading_month' => 'Fulbaana', 'sync_status' => 'Synced', 'reading_branch' => 'Eteya'],
            ['meter_reading_id' => 'RD-006', 'meter_serial' => 'ETY-0006', 'reading_date' => '2024-09-30', 'current_reading' => 42.00,   'collector' => 'Eyasu', 'reading_year' => '2017', 'reading_month' => 'Fulbaana', 'sync_status' => 'Synced', 'reading_branch' => 'Eteya'],
            ['meter_reading_id' => 'RD-007', 'meter_serial' => 'ETY-0007', 'reading_date' => '2024-09-30', 'current_reading' => 1010.00, 'collector' => 'Eyasu', 'reading_year' => '2017', 'reading_month' => 'Fulbaana', 'sync_status' => 'Synced', 'reading_branch' => 'Eteya'],
            ['meter_reading_id' => 'RD-008', 'meter_serial' => 'ETY-0008', 'reading_date' => '2024-09-30', 'current_reading' => 87.00,   'collector' => 'Eyasu', 'reading_year' => '2017', 'reading_month' => 'Fulbaana', 'sync_status' => 'Synced', 'reading_branch' => 'Eteya'],
            ['meter_reading_id' => 'RD-009', 'meter_serial' => 'ETY-0009', 'reading_date' => '2024-09-30', 'current_reading' => 175.00,  'collector' => 'Eyasu', 'reading_year' => '2017', 'reading_month' => 'Fulbaana', 'sync_status' => 'Synced', 'reading_branch' => 'Eteya'],
            ['meter_reading_id' => 'RD-010', 'meter_serial' => 'ETY-0010', 'reading_date' => '2024-09-30', 'current_reading' => 78.00,   'collector' => 'Eyasu', 'reading_year' => '2017', 'reading_month' => 'Fulbaana', 'sync_status' => 'Synced', 'reading_branch' => 'Eteya'],
        ];

        foreach ($readings as $r) {
            SeasonalConsumption::updateOrCreate(['meter_reading_id' => $r['meter_reading_id']], $r);
        }
    }
}

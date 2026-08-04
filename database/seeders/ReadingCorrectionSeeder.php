<?php

namespace Database\Seeders;

use App\Models\ReadingCorrection;
use Illuminate\Database\Seeder;

class ReadingCorrectionSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['customer_code' => 'ETY-0001', 'reading_year' => '2017', 'reading_month' => 'Fulbaana', 'sending_department' => 'Chaltu Tola', 'complain_date_time' => '2024-10-05 10:30:00', 'correction_status' => 'Approved', 'new_reading' => '108',          'approved_name' => 'System Admin', 'sync_status' => 'Synced'],
            ['customer_code' => 'ETY-0003', 'reading_year' => '2017', 'reading_month' => 'Fulbaana', 'sending_department' => 'Chaltu Tola', 'complain_date_time' => '2024-10-06 14:15:00', 'correction_status' => 'Pending',  'new_reading' => 'NotInserted',  'approved_name' => 'Pending',      'sync_status' => 'New'],
        ];

        foreach ($rows as $r) {
            ReadingCorrection::create($r);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\OperationAuditing;
use Illuminate\Database\Seeder;

class OperationAuditingSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['log_date' => '2024-10-05 09:00:00', 'log_reason' => 'Registered new customer ETY-0001',           'done_by' => 'System Administrator'],
            ['log_date' => '2024-10-05 09:30:00', 'log_reason' => 'Updated phone number for ETY-0003',         'done_by' => 'System Administrator'],
        ];

        foreach ($rows as $r) {
            OperationAuditing::create($r);
        }
    }
}

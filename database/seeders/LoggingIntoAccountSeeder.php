<?php

namespace Database\Seeders;

use App\Models\LoggingIntoAccount;
use Illuminate\Database\Seeder;

class LoggingIntoAccountSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['log_date' => '2024-10-05 08:00:00', 'user' => 'System Administrator', 'task' => 'Logging to System'],
            ['log_date' => '2024-10-05 17:00:00', 'user' => 'System Administrator', 'task' => 'Logout to System'],
        ];

        foreach ($rows as $r) {
            LoggingIntoAccount::create($r);
        }
    }
}

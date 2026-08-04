<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            JobRoleSeeder::class,
            SettingsSeeder::class,
            UserAccountSeeder::class,
            WorkingPeriodSeeder::class,
            ActiveCustomerSeeder::class,
            SeasonalConsumptionSeeder::class,
            BillFinanceSeeder::class,
            ReadingCorrectionSeeder::class,
            OperationAuditingSeeder::class,
            LoggingIntoAccountSeeder::class,
        ]);
    }
}

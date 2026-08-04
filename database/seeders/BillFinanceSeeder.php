<?php

namespace Database\Seeders;

use App\Models\BillFinance;
use Illuminate\Database\Seeder;

class BillFinanceSeeder extends Seeder
{
    public function run(): void
    {
        $bills = [
            ['bill_finance_id' => 'BF-0001-2017-FUL', 'meter_serial' => 'ETY-0001', 'meter_price' => 5.00,  'service_price' => 2.00, 'consumption' => 10.00, 'penalty_cost' => 0.00, 'community_cost' => 1.00, 'total_monthly_cost' => 53.00,  'consumption_cost' => 45.00,  'total_aggregation_cost' => 53.00,  'deposited_cost' => 0.00, 'payment_status' => 'Unpaid', 'bill_year' => '2017', 'bill_month' => 'Fulbaana', 'state_price' => 5.00,  'calculate_status' => 'Calculated', 'bill_period' => '2017 - Fulbaana', 'full_name' => 'Tesfaye Alemu Girma',         'kebele' => '01', 'meter_num' => 1,  'customer_type' => 'Dhunfaa',                 'customer_branch' => 'Eteya'],
            ['bill_finance_id' => 'BF-0002-2017-FUL', 'meter_serial' => 'ETY-0002', 'meter_price' => 5.00,  'service_price' => 2.00, 'consumption' => 15.00, 'penalty_cost' => 0.00, 'community_cost' => 1.00, 'total_monthly_cost' => 67.50,  'consumption_cost' => 59.50,  'total_aggregation_cost' => 67.50,  'deposited_cost' => 0.00, 'payment_status' => 'Paid',   'bill_year' => '2017', 'bill_month' => 'Fulbaana', 'state_price' => 5.00,  'calculate_status' => 'Calculated', 'bill_period' => '2017 - Fulbaana', 'full_name' => 'Chaltu Tesfaye Bori',         'kebele' => '01', 'meter_num' => 2,  'customer_type' => 'Dhunfaa',                 'customer_branch' => 'Eteya', 'print_date' => '2024-10-05', 'print_person' => 'Abebe', 'bill_number' => 'B-001', 'window_number' => 'W1'],
            ['bill_finance_id' => 'BF-0003-2017-FUL', 'meter_serial' => 'ETY-0003', 'meter_price' => 8.00,  'service_price' => 5.00, 'consumption' => 15.00, 'penalty_cost' => 0.00, 'community_cost' => 2.00, 'total_monthly_cost' => 96.50,  'consumption_cost' => 81.50,  'total_aggregation_cost' => 96.50,  'deposited_cost' => 0.00, 'payment_status' => 'Unpaid', 'bill_year' => '2017', 'bill_month' => 'Fulbaana', 'state_price' => 8.00,  'calculate_status' => 'Calculated', 'bill_period' => '2017 - Fulbaana', 'full_name' => 'Gutema Tola Roba',            'kebele' => '02', 'meter_num' => 3,  'customer_type' => 'Daldaltoota fi Industry','customer_branch' => 'Eteya'],
            ['bill_finance_id' => 'BF-0004-2017-FUL', 'meter_serial' => 'ETY-0004', 'meter_price' => 5.00,  'service_price' => 2.00, 'consumption' => 12.00, 'penalty_cost' => 0.00, 'community_cost' => 1.00, 'total_monthly_cost' => 58.00,  'consumption_cost' => 50.00,  'total_aggregation_cost' => 58.00,  'deposited_cost' => 0.00, 'payment_status' => 'Unpaid', 'bill_year' => '2017', 'bill_month' => 'Fulbaana', 'state_price' => 5.00,  'calculate_status' => 'Calculated', 'bill_period' => '2017 - Fulbaana', 'full_name' => 'Hawi Deme Wako',              'kebele' => '02', 'meter_num' => 4,  'customer_type' => 'Dhunfaa',                 'customer_branch' => 'Eteya'],
            ['bill_finance_id' => 'BF-0005-2017-FUL', 'meter_serial' => 'ETY-0005', 'meter_price' => 5.00,  'service_price' => 2.00, 'consumption' => 5.00,  'penalty_cost' => 0.00, 'community_cost' => 1.00, 'total_monthly_cost' => 27.50,  'consumption_cost' => 19.50,  'total_aggregation_cost' => 27.50,  'deposited_cost' => 0.00, 'payment_status' => 'Unpaid', 'bill_year' => '2017', 'bill_month' => 'Fulbaana', 'state_price' => 5.00,  'calculate_status' => 'Calculated', 'bill_period' => '2017 - Fulbaana', 'full_name' => 'Ibsa Tesfaye Lencho',        'kebele' => '03', 'meter_num' => 5,  'customer_type' => 'Waajjira Motummaa',       'customer_branch' => 'Eteya'],
        ];

        foreach ($bills as $b) {
            BillFinance::updateOrCreate(['bill_finance_id' => $b['bill_finance_id']], $b);
        }
    }
}

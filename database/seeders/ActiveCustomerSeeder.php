<?php

namespace Database\Seeders;

use App\Models\ActiveCustomer;
use Illuminate\Database\Seeder;

class ActiveCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['meter_serial' => 'ETY-0001', 'first_name' => 'Tesfaye',  'middle_name' => 'Alemu',   'last_name' => 'Girma',  'kebele' => '01', 'sold_date' => '2024-01-15', 'meter_num' => 1,  'meter_size' => '1/2"', 'customer_type' => 'Dhunfaa',                  'bill_num' => 'SN-0001', 'phone_number' => '+251911223344', 'start_value' => 100.00,  'payment_way' => 'BANK',     'customer_branch' => 'Eteya', 'customer_status' => 'Active', 'sync_status' => 'Synced', 'reader_block' => 'Block-A'],
            ['meter_serial' => 'ETY-0002', 'first_name' => 'Chaltu',   'middle_name' => 'Tesfaye', 'last_name' => 'Bori',   'kebele' => '01', 'sold_date' => '2024-02-10', 'meter_num' => 2,  'meter_size' => '1/2"', 'customer_type' => 'Dhunfaa',                  'bill_num' => 'SN-0002', 'phone_number' => '+251911445566', 'start_value' => 50.00,   'payment_way' => 'NON_BANK', 'customer_branch' => 'Eteya', 'customer_status' => 'Active', 'sync_status' => 'Synced', 'reader_block' => 'Block-A'],
            ['meter_serial' => 'ETY-0003', 'first_name' => 'Gutema',   'middle_name' => 'Tola',    'last_name' => 'Roba',  'kebele' => '02', 'sold_date' => '2024-03-05', 'meter_num' => 3,  'meter_size' => '3/4"', 'customer_type' => 'Daldaltoota fi Industry',  'bill_num' => 'SN-0003', 'phone_number' => '+251911778899', 'start_value' => 200.00,  'payment_way' => 'BANK',     'customer_branch' => 'Eteya', 'customer_status' => 'Active', 'sync_status' => 'Synced', 'reader_block' => 'Block-B'],
            ['meter_serial' => 'ETY-0004', 'first_name' => 'Hawi',     'middle_name' => 'Deme',    'last_name' => 'Wako',  'kebele' => '02', 'sold_date' => '2024-04-12', 'meter_num' => 4,  'meter_size' => '1/2"', 'customer_type' => 'Dhunfaa',                  'bill_num' => 'SN-0004', 'phone_number' => '+251912334455', 'start_value' => 0.00,    'payment_way' => 'NON_BANK', 'customer_branch' => 'Eteya', 'customer_status' => 'Active', 'sync_status' => 'Synced', 'reader_block' => 'Block-B'],
            ['meter_serial' => 'ETY-0005', 'first_name' => 'Ibsa',      'middle_name' => 'Tesfaye', 'last_name' => 'Lencho','kebele' => '03', 'sold_date' => '2024-05-20', 'meter_num' => 5,  'meter_size' => '1"',   'customer_type' => 'Waajjira Motummaa',        'bill_num' => 'SN-0005', 'phone_number' => '+251913667788', 'start_value' => 500.00,  'payment_way' => 'BANK',     'customer_branch' => 'Eteya', 'customer_status' => 'DC',     'sync_status' => 'Synced', 'reader_block' => 'Block-C'],
            ['meter_serial' => 'ETY-0006', 'first_name' => 'Jitu',      'middle_name' => 'Girma',   'last_name' => 'Wendimu','kebele' => '03','sold_date' => '2024-06-01', 'meter_num' => 6,  'meter_size' => '1/2"', 'customer_type' => 'Dhunfaa',                  'bill_num' => 'SN-0006', 'phone_number' => '+251914889900', 'start_value' => 30.00,   'payment_way' => 'NON_BANK', 'customer_branch' => 'Eteya', 'customer_status' => 'Active', 'sync_status' => 'Synced', 'reader_block' => 'Block-C'],
            ['meter_serial' => 'ETY-0007', 'first_name' => 'Kuma',      'middle_name' => 'Berhanu', 'last_name' => 'Demissie','kebele' => '04','sold_date' => '2024-07-15', 'meter_num' => 7, 'meter_size' => '2"',   'customer_type' => 'Waajjira Miti-Motummaa',   'bill_num' => 'SN-0007', 'phone_number' => '+251915112233', 'start_value' => 1000.00, 'payment_way' => 'BANK',     'customer_branch' => 'Eteya', 'customer_status' => 'Active', 'sync_status' => 'Synced', 'reader_block' => 'Block-D'],
            ['meter_serial' => 'ETY-0008', 'first_name' => 'Lemi',      'middle_name' => 'Tadesse', 'last_name' => 'Gemechu','kebele' => '04','sold_date' => '2024-08-22', 'meter_num' => 8, 'meter_size' => '1/2"', 'customer_type' => 'Boonoo',                   'bill_num' => 'SN-0008', 'phone_number' => '+251916334455', 'start_value' => 75.00,   'payment_way' => 'NON_BANK', 'customer_branch' => 'Eteya', 'customer_status' => 'Active', 'sync_status' => 'Synced', 'reader_block' => 'Block-D'],
            ['meter_serial' => 'ETY-0009', 'first_name' => 'Marta',     'middle_name' => 'Bekele',  'last_name' => 'Tola',   'kebele' => '05','sold_date' => '2024-09-10', 'meter_num' => 9, 'meter_size' => '3/4"', 'customer_type' => 'Daldaltoota fi Industry',  'bill_num' => 'SN-0009', 'phone_number' => '+251917556677', 'start_value' => 150.00,  'payment_way' => 'BANK',     'customer_branch' => 'Eteya', 'customer_status' => 'Active', 'sync_status' => 'Synced', 'reader_block' => 'Block-E'],
            ['meter_serial' => 'ETY-0010', 'first_name' => 'Nagassa',  'middle_name' => 'Teshome', 'last_name' => 'Haile',  'kebele' => '05','sold_date' => '2024-10-05', 'meter_num' => 10,'meter_size' => '1/2"', 'customer_type' => 'Dhunfaa',                  'bill_num' => 'SN-0010', 'phone_number' => '+251918778899', 'start_value' => 60.00,   'payment_way' => 'BANK',     'customer_branch' => 'Eteya', 'customer_status' => 'Active', 'sync_status' => 'Synced', 'reader_block' => 'Block-E'],
        ];

        foreach ($customers as $c) {
            ActiveCustomer::updateOrCreate(['meter_serial' => $c['meter_serial']], $c);
        }
    }
}

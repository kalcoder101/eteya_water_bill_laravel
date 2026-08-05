<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'enterprise_name_en' => 'HHD Water Supply and Sewerage Service Enterprise',
            'enterprise_name_or' => "Dhaabbata Tajaajila Bishaan Dhugaatii fi Dhangala'aa",
            'town_name'          => 'WaterSteward',
            'default_branch'     => 'WaterSteward',
            'developer_credit'   => 'Designed & Developed By: GITAN ICT Work PLC, Phone: +251-967-67-1810, +251-907-60-6050',
            'bill_slogan'        => 'Bishaan Lubbuu Dha!!!',
            'app_version'         => '1.0.0 Laravel',
            'current_bill_year'  => '2017',
            'current_bill_month'  => 'Fulbaana',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }
    }
}

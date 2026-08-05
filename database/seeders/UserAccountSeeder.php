<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAccountSeeder extends Seeder
{
    public function run(): void
    {
        // NOTE: passwords are stored as plain text in the seed data to match
        // the original app's behaviour (admin/admin123 etc.) — the AuthController
        // transparently rehashes them on first successful login.
        $users = [
            ['user_id' => 'EMP001', 'first_name' => 'System',     'last_name' => 'Administrator', 'phone_number' => '+251900000001', 'email_id' => 'admin@watersteward.gov.et',  'job_role' => 'System Admin',     'user_name' => 'admin',  'user_password' => 'admin123'],
            ['user_id' => 'EMP002', 'first_name' => 'Customer',   'last_name' => 'Service',        'phone_number' => '+251900000002', 'email_id' => 'cs@watersteward.gov.et',     'job_role' => 'Customer Service', 'user_name' => 'cs',      'user_password' => 'cs123'],
            ['user_id' => 'EMP003', 'first_name' => 'Abebe',       'last_name' => 'Bekele',         'phone_number' => '+251900000003', 'email_id' => 'abebe@watersteward.gov.et',   'job_role' => 'Customer Service', 'user_name' => 'abebe',  'user_password' => 'abebe123'],
            ['user_id' => 'EMP004', 'first_name' => 'Chaltu',      'last_name' => 'Tola',           'phone_number' => '+251900000004', 'email_id' => 'chaltu@watersteward.gov.et',  'job_role' => 'Manager',          'user_name' => 'chaltu', 'user_password' => 'chaltu123'],
            ['user_id' => 'EMP005', 'first_name' => 'Dereje',      'last_name' => 'Gemechu',        'phone_number' => '+251900000005', 'email_id' => 'dereje@watersteward.gov.et',  'job_role' => 'Secretary',         'user_name' => 'dereje', 'user_password' => 'dereje123'],
            ['user_id' => 'EMP006', 'first_name' => 'Eyasu',       'last_name' => 'Hailu',          'phone_number' => '+251900000006', 'email_id' => 'eyasu@watersteward.gov.et',   'job_role' => 'Bill Reader',       'user_name' => 'eyasu',  'user_password' => 'eyasu123'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['user_id' => $u['user_id']], $u);
        }
    }
}

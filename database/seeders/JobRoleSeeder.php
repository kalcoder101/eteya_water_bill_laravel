<?php

namespace Database\Seeders;

use App\Models\JobRole;
use Illuminate\Database\Seeder;

class JobRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['role_name' => 'System Admin',       'display_name' => 'System Administrator',     'color_badge' => 'badge-danger',   'is_active' => true],
            ['role_name' => 'Manager',            'display_name' => 'Operations Manager',       'color_badge' => 'badge-warning',  'is_active' => true],
            ['role_name' => 'Customer Service',   'display_name' => 'Customer Service Officer', 'color_badge' => 'badge-info',     'is_active' => true],
            ['role_name' => 'Secretary',          'display_name' => 'Secretary',                'color_badge' => 'badge-success',  'is_active' => true],
            ['role_name' => 'Bill Reader',         'display_name' => 'Meter Reader',             'color_badge' => 'badge-primary',  'is_active' => true],
            ['role_name' => 'Finance Officer',    'display_name' => 'Finance & Accounts',       'color_badge' => 'badge-secondary','is_active' => true],
            ['role_name' => 'Operations Officer', 'display_name' => 'Field Operations',         'color_badge' => 'badge-success',  'is_active' => true],
        ];

        foreach ($roles as $role) {
            JobRole::updateOrCreate(['role_name' => $role['role_name']], $role);
        }
    }
}

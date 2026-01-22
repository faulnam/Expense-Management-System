<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $employeeRole = Role::where('slug', 'employee')->first();
        $managerRole = Role::where('slug', 'manager')->first();
        $financeRole = Role::where('slug', 'finance')->first();

        // Create Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@company.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'employee_id' => 'EMP001',
                'department' => 'IT',
                'position' => 'System Administrator',
                'phone' => '081234567890',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Manager - IT Department
        $managerIT = User::firstOrCreate(
            ['email' => 'manager.it@company.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'role_id' => $managerRole->id,
                'employee_id' => 'EMP002',
                'department' => 'IT',
                'position' => 'IT Manager',
                'phone' => '081234567891',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Manager - Sales Department
        $managerSales = User::firstOrCreate(
            ['email' => 'manager.sales@company.com'],
            [
                'name' => 'Dewi Lestari',
                'password' => Hash::make('password'),
                'role_id' => $managerRole->id,
                'employee_id' => 'EMP003',
                'department' => 'Sales',
                'position' => 'Sales Manager',
                'phone' => '081234567892',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Manager - Operations Department
        $managerOps = User::firstOrCreate(
            ['email' => 'manager.ops@company.com'],
            [
                'name' => 'Ahmad Wijaya',
                'password' => Hash::make('password'),
                'role_id' => $managerRole->id,
                'employee_id' => 'EMP004',
                'department' => 'Operations',
                'position' => 'Operations Manager',
                'phone' => '081234567893',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Finance Staff
        $finance1 = User::firstOrCreate(
            ['email' => 'finance@company.com'],
            [
                'name' => 'Siti Rahayu',
                'password' => Hash::make('password'),
                'role_id' => $financeRole->id,
                'employee_id' => 'EMP005',
                'department' => 'Finance',
                'position' => 'Finance Officer',
                'phone' => '081234567894',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $finance2 = User::firstOrCreate(
            ['email' => 'finance2@company.com'],
            [
                'name' => 'Rini Kusuma',
                'password' => Hash::make('password'),
                'role_id' => $financeRole->id,
                'employee_id' => 'EMP006',
                'department' => 'Finance',
                'position' => 'Finance Staff',
                'phone' => '081234567895',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Employees - IT Department
        User::firstOrCreate(
            ['email' => 'employee.it1@company.com'],
            [
                'name' => 'Agus Pratama',
                'password' => Hash::make('password'),
                'role_id' => $employeeRole->id,
                'employee_id' => 'EMP007',
                'department' => 'IT',
                'position' => 'Software Developer',
                'manager_id' => $managerIT->id,
                'phone' => '081234567896',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'employee.it2@company.com'],
            [
                'name' => 'Rina Marlina',
                'password' => Hash::make('password'),
                'role_id' => $employeeRole->id,
                'employee_id' => 'EMP008',
                'department' => 'IT',
                'position' => 'QA Engineer',
                'manager_id' => $managerIT->id,
                'phone' => '081234567897',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Employees - Sales Department
        User::firstOrCreate(
            ['email' => 'employee.sales1@company.com'],
            [
                'name' => 'Joko Susanto',
                'password' => Hash::make('password'),
                'role_id' => $employeeRole->id,
                'employee_id' => 'EMP009',
                'department' => 'Sales',
                'position' => 'Sales Executive',
                'manager_id' => $managerSales->id,
                'phone' => '081234567898',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'employee.sales2@company.com'],
            [
                'name' => 'Maya Putri',
                'password' => Hash::make('password'),
                'role_id' => $employeeRole->id,
                'employee_id' => 'EMP010',
                'department' => 'Sales',
                'position' => 'Account Manager',
                'manager_id' => $managerSales->id,
                'phone' => '081234567899',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Employees - Operations Department
        User::firstOrCreate(
            ['email' => 'employee.ops1@company.com'],
            [
                'name' => 'Hendra Gunawan',
                'password' => Hash::make('password'),
                'role_id' => $employeeRole->id,
                'employee_id' => 'EMP011',
                'department' => 'Operations',
                'position' => 'Operations Staff',
                'manager_id' => $managerOps->id,
                'phone' => '081234567800',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'employee.ops2@company.com'],
            [
                'name' => 'Fitri Handayani',
                'password' => Hash::make('password'),
                'role_id' => $employeeRole->id,
                'employee_id' => 'EMP012',
                'department' => 'Operations',
                'position' => 'Logistics Coordinator',
                'manager_id' => $managerOps->id,
                'phone' => '081234567801',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}

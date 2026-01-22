<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full system access including user management and system configuration',
            ],
            [
                'name' => 'Employee',
                'slug' => 'employee',
                'description' => 'Can submit and track personal expense claims',
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Can review and approve/reject subordinate expense claims',
            ],
            [
                'name' => 'Finance',
                'slug' => 'finance',
                'description' => 'Can process payments and generate financial reports',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(['slug' => $roleData['slug']], $roleData);
        }

        // Create Permissions
        $permissions = [
            // User Management
            ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'Can view user list'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'description' => 'Can create new users'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'description' => 'Can edit existing users'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'description' => 'Can delete users'],
            
            // Category Management
            ['name' => 'View Categories', 'slug' => 'categories.view', 'description' => 'Can view expense categories'],
            ['name' => 'Manage Categories', 'slug' => 'categories.manage', 'description' => 'Can manage expense categories'],
            
            // Budget Management
            ['name' => 'View Budgets', 'slug' => 'budgets.view', 'description' => 'Can view budgets'],
            ['name' => 'Manage Budgets', 'slug' => 'budgets.manage', 'description' => 'Can manage budgets'],
            
            // Expense Management
            ['name' => 'View Own Expenses', 'slug' => 'expenses.view.own', 'description' => 'Can view own expenses'],
            ['name' => 'View All Expenses', 'slug' => 'expenses.view.all', 'description' => 'Can view all expenses'],
            ['name' => 'View Team Expenses', 'slug' => 'expenses.view.team', 'description' => 'Can view team expenses'],
            ['name' => 'Create Expenses', 'slug' => 'expenses.create', 'description' => 'Can create expense claims'],
            ['name' => 'Edit Own Expenses', 'slug' => 'expenses.edit.own', 'description' => 'Can edit own expense claims'],
            ['name' => 'Delete Own Expenses', 'slug' => 'expenses.delete.own', 'description' => 'Can delete own expense claims'],
            
            // Approval
            ['name' => 'Approve Expenses', 'slug' => 'expenses.approve', 'description' => 'Can approve expense claims'],
            ['name' => 'Reject Expenses', 'slug' => 'expenses.reject', 'description' => 'Can reject expense claims'],
            
            // Payment
            ['name' => 'View Payments', 'slug' => 'payments.view', 'description' => 'Can view payment records'],
            ['name' => 'Process Payments', 'slug' => 'payments.process', 'description' => 'Can process expense payments'],
            
            // Reporting
            ['name' => 'View Own Reports', 'slug' => 'reports.view.own', 'description' => 'Can view own reports'],
            ['name' => 'View All Reports', 'slug' => 'reports.view.all', 'description' => 'Can view all reports'],
            ['name' => 'View Team Reports', 'slug' => 'reports.view.team', 'description' => 'Can view team reports'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'description' => 'Can export reports'],
            
            // Audit
            ['name' => 'View Audit Logs', 'slug' => 'audit.view', 'description' => 'Can view audit logs'],
            
            // Fraud Management
            ['name' => 'Flag Expenses', 'slug' => 'expenses.flag', 'description' => 'Can flag suspicious expenses'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(['slug' => $permissionData['slug']], $permissionData);
        }

        // Assign Permissions to Roles
        $adminRole = Role::where('slug', 'admin')->first();
        $employeeRole = Role::where('slug', 'employee')->first();
        $managerRole = Role::where('slug', 'manager')->first();
        $financeRole = Role::where('slug', 'finance')->first();

        // Admin - All permissions
        $adminRole->permissions()->sync(Permission::pluck('id'));

        // Employee permissions
        $employeePermissions = Permission::whereIn('slug', [
            'expenses.view.own',
            'expenses.create',
            'expenses.edit.own',
            'expenses.delete.own',
            'reports.view.own',
        ])->pluck('id');
        $employeeRole->permissions()->sync($employeePermissions);

        // Manager permissions
        $managerPermissions = Permission::whereIn('slug', [
            'expenses.view.own',
            'expenses.view.team',
            'expenses.create',
            'expenses.edit.own',
            'expenses.delete.own',
            'expenses.approve',
            'expenses.reject',
            'reports.view.own',
            'reports.view.team',
        ])->pluck('id');
        $managerRole->permissions()->sync($managerPermissions);

        // Finance permissions
        $financePermissions = Permission::whereIn('slug', [
            'expenses.view.all',
            'payments.view',
            'payments.process',
            'reports.view.all',
            'reports.export',
            'expenses.flag',
            'budgets.view',
        ])->pluck('id');
        $financeRole->permissions()->sync($financePermissions);
    }
}

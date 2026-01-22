<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Budget;
use App\Models\ExpenseCategory;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ExpenseCategory::all();
        $departments = ['IT', 'Sales', 'Operations', 'Finance'];
        $currentYear = now()->year;
        $currentMonth = now()->month;

        foreach ($categories as $category) {
            foreach ($departments as $department) {
                // Create budget for current month
                Budget::firstOrCreate(
                    [
                        'category_id' => $category->id,
                        'department' => $department,
                        'year' => $currentYear,
                        'month' => $currentMonth,
                    ],
                    [
                        'limit_amount' => $category->default_limit,
                        'used_amount' => 0,
                        'warning_threshold' => 80.00,
                        'is_active' => true,
                    ]
                );

                // Create budget for next month
                $nextMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
                $nextYear = $currentMonth == 12 ? $currentYear + 1 : $currentYear;
                
                Budget::firstOrCreate(
                    [
                        'category_id' => $category->id,
                        'department' => $department,
                        'year' => $nextYear,
                        'month' => $nextMonth,
                    ],
                    [
                        'limit_amount' => $category->default_limit,
                        'used_amount' => 0,
                        'warning_threshold' => 80.00,
                        'is_active' => true,
                    ]
                );
            }

            // Create global budget (no department) for current month
            Budget::firstOrCreate(
                [
                    'category_id' => $category->id,
                    'department' => null,
                    'year' => $currentYear,
                    'month' => $currentMonth,
                ],
                [
                    'limit_amount' => $category->default_limit * 4,
                    'used_amount' => 0,
                    'warning_threshold' => 80.00,
                    'is_active' => true,
                ]
            );
        }
    }
}

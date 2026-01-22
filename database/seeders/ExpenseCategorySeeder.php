<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Transportation',
                'code' => 'TRANS',
                'description' => 'Travel expenses including taxi, fuel, parking, and public transportation',
                'default_limit' => 2000000,
                'requires_receipt' => true,
            ],
            [
                'name' => 'Meals & Entertainment',
                'code' => 'MEAL',
                'description' => 'Business meals, client entertainment, and team meals',
                'default_limit' => 1500000,
                'requires_receipt' => true,
            ],
            [
                'name' => 'Office Supplies',
                'code' => 'SUPPLY',
                'description' => 'Stationery, printing, and office equipment under Rp 500,000',
                'default_limit' => 500000,
                'requires_receipt' => true,
            ],
            [
                'name' => 'Communication',
                'code' => 'COMM',
                'description' => 'Phone bills, internet, and communication related expenses',
                'default_limit' => 500000,
                'requires_receipt' => true,
            ],
            [
                'name' => 'Training & Development',
                'code' => 'TRAIN',
                'description' => 'Courses, seminars, certifications, and professional development',
                'default_limit' => 5000000,
                'requires_receipt' => true,
            ],
            [
                'name' => 'Accommodation',
                'code' => 'ACCOM',
                'description' => 'Hotel stays and lodging for business trips',
                'default_limit' => 3000000,
                'requires_receipt' => true,
            ],
            [
                'name' => 'Medical',
                'code' => 'MED',
                'description' => 'Medical expenses not covered by insurance',
                'default_limit' => 1000000,
                'requires_receipt' => true,
            ],
            [
                'name' => 'Client Expenses',
                'code' => 'CLIENT',
                'description' => 'Expenses related to client visits and client services',
                'default_limit' => 2500000,
                'requires_receipt' => true,
            ],
            [
                'name' => 'Miscellaneous',
                'code' => 'MISC',
                'description' => 'Other business expenses not categorized elsewhere',
                'default_limit' => 500000,
                'requires_receipt' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            ExpenseCategory::firstOrCreate(
                ['code' => $categoryData['code']],
                $categoryData
            );
        }
    }
}

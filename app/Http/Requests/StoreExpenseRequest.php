<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:expense_categories,id',
            'expense_date' => 'required|date|before_or_equal:today',
            'amount' => 'required|numeric|min:1000|max:100000000',
            'description' => 'required|string|min:10|max:1000',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select an expense category.',
            'category_id.exists' => 'The selected category is invalid.',
            'expense_date.required' => 'Please enter the expense date.',
            'expense_date.before_or_equal' => 'Expense date cannot be in the future.',
            'amount.required' => 'Please enter the expense amount.',
            'amount.min' => 'Minimum expense amount is Rp 1,000.',
            'amount.max' => 'Maximum expense amount is Rp 100,000,000.',
            'description.required' => 'Please provide a description.',
            'description.min' => 'Description must be at least 10 characters.',
            'receipt.mimes' => 'Receipt must be a JPG, PNG, or PDF file.',
            'receipt.max' => 'Receipt file size must not exceed 5MB.',
        ];
    }
}

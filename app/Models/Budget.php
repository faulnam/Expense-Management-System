<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'department',
        'year',
        'month',
        'limit_amount',
        'used_amount',
        'warning_threshold',
        'is_active',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'limit_amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
        'warning_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->limit_amount - $this->used_amount);
    }

    public function getUsagePercentageAttribute(): float
    {
        if ($this->limit_amount <= 0) {
            return 0;
        }
        return round(($this->used_amount / $this->limit_amount) * 100, 2);
    }

    public function isOverBudget(): bool
    {
        return $this->used_amount > $this->limit_amount;
    }

    public function isNearLimit(): bool
    {
        return $this->usage_percentage >= $this->warning_threshold;
    }

    public function getStatusAttribute(): string
    {
        if ($this->isOverBudget()) {
            return 'over_budget';
        }
        if ($this->isNearLimit()) {
            return 'warning';
        }
        return 'normal';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'over_budget' => 'bg-red-100 text-red-800',
            'warning' => 'bg-yellow-100 text-yellow-800',
            'normal' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function scopeForPeriod($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeForDepartment($query, ?string $department)
    {
        if ($department) {
            return $query->where('department', $department);
        }
        return $query->whereNull('department');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function findForExpense(Expense $expense): ?self
    {
        return self::forCategory($expense->category_id)
            ->forDepartment($expense->user->department)
            ->forPeriod($expense->expense_date->year, $expense->expense_date->month)
            ->active()
            ->first();
    }
}

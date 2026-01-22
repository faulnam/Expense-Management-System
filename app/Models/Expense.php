<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_number',
        'user_id',
        'category_id',
        'expense_date',
        'amount',
        'description',
        'receipt_path',
        'receipt_original_name',
        'status',
        'rejection_reason',
        'is_flagged',
        'flag_reason',
        'flagged_by',
        'flagged_at',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'paid_at',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'is_flagged' => 'boolean',
        'flagged_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_MANAGER_APPROVED = 'manager_approved';
    const STATUS_FINANCE_APPROVED = 'finance_approved';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

    public static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = self::generateExpenseNumber();
            }
        });
    }

    public static function generateExpenseNumber(): string
    {
        $prefix = 'EXP';
        $date = now()->format('Ymd');
        $lastExpense = self::withTrashed()
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastExpense ? (int)substr($lastExpense->expense_number, -4) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function flaggedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'flagged_by');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDepartment($query, string $department)
    {
        return $query->whereHas('user', fn($q) => $q->where('department', $department));
    }

    public function scopePeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isManagerApproved(): bool
    {
        return $this->status === self::STATUS_MANAGER_APPROVED;
    }

    public function isFinanceApproved(): bool
    {
        return $this->status === self::STATUS_FINANCE_APPROVED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft() || $this->isRejected();
    }

    public function canBeSubmitted(): bool
    {
        return $this->isDraft();
    }

    public function canBeApproved(): bool
    {
        return $this->isSubmitted();
    }

    public function canBeApprovedByManager(): bool
    {
        return $this->isSubmitted() && !$this->is_flagged;
    }

    public function canBeApprovedByFinance(): bool
    {
        return $this->isManagerApproved() && !$this->is_flagged;
    }

    public function canBePaid(): bool
    {
        return $this->isApproved() || $this->isFinanceApproved();
    }

    public function canBeRejected(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUBMITTED,
            self::STATUS_MANAGER_APPROVED,
            self::STATUS_FINANCE_APPROVED,
        ]);
    }

    public function canBeFlagged(): bool
    {
        return !$this->isPaid() && !$this->isRejected();
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_path) {
            return null;
        }
        return asset('storage/' . $this->receipt_path);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_SUBMITTED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_MANAGER_APPROVED => 'bg-blue-100 text-blue-800',
            self::STATUS_FINANCE_APPROVED => 'bg-indigo-100 text-indigo-800',
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
            self::STATUS_PAID => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Pending Manager',
            self::STATUS_MANAGER_APPROVED => 'Pending Finance',
            self::STATUS_FINANCE_APPROVED => 'Awaiting Payment',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PAID => 'Paid',
            default => 'Unknown',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get pending manager approval expenses
     */
    public function scopePendingManagerApproval($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    /**
     * Get pending finance approval expenses
     */
    public function scopePendingFinanceApproval($query)
    {
        return $query->where('status', self::STATUS_MANAGER_APPROVED);
    }

    /**
     * Get pending payment expenses
     */
    public function scopePendingPayment($query)
    {
        return $query->whereIn('status', [self::STATUS_FINANCE_APPROVED, self::STATUS_APPROVED]);
    }

    /**
     * Get flagged expenses
     */
    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    /**
     * Get this month expenses
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }
}

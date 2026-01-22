<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use App\Models\Budget;
use App\Models\ExpenseCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getExpenseSummary(
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $userId = null,
        ?string $department = null,
        ?int $categoryId = null
    ): array {
        $query = Expense::query()
            ->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_PAID]);

        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($department) {
            $query->whereHas('user', fn($q) => $q->where('department', $department));
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $expenses = $query->get();

        return [
            'total_amount' => $expenses->sum('amount'),
            'total_count' => $expenses->count(),
            'average_amount' => $expenses->avg('amount') ?? 0,
            'by_status' => [
                'approved' => $expenses->where('status', Expense::STATUS_APPROVED)->sum('amount'),
                'paid' => $expenses->where('status', Expense::STATUS_PAID)->sum('amount'),
            ],
        ];
    }

    public function getExpensesByCategory(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $department = null
    ): Collection {
        $query = Expense::query()
            ->select('category_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_PAID])
            ->groupBy('category_id')
            ->with('category');

        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        }

        if ($department) {
            $query->whereHas('user', fn($q) => $q->where('department', $department));
        }

        return $query->get()->map(function ($item) {
            return [
                'category_id' => $item->category_id,
                'category_name' => $item->category->name ?? 'Unknown',
                'category_code' => $item->category->code ?? 'N/A',
                'total_amount' => $item->total_amount,
                'count' => $item->count,
            ];
        });
    }

    public function getExpensesByDepartment(
        ?string $startDate = null,
        ?string $endDate = null
    ): Collection {
        $query = Expense::query()
            ->select('users.department', DB::raw('SUM(expenses.amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->join('users', 'expenses.user_id', '=', 'users.id')
            ->whereIn('expenses.status', [Expense::STATUS_APPROVED, Expense::STATUS_PAID])
            ->whereNotNull('users.department')
            ->groupBy('users.department');

        if ($startDate && $endDate) {
            $query->whereBetween('expenses.expense_date', [$startDate, $endDate]);
        }

        return $query->get();
    }

    public function getExpensesByEmployee(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $department = null
    ): Collection {
        $query = Expense::query()
            ->select('user_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_PAID])
            ->groupBy('user_id')
            ->with('user');

        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        }

        if ($department) {
            $query->whereHas('user', fn($q) => $q->where('department', $department));
        }

        return $query->get()->map(function ($item) {
            return [
                'user_id' => $item->user_id,
                'user_name' => $item->user->name ?? 'Unknown',
                'department' => $item->user->department ?? 'N/A',
                'total_amount' => $item->total_amount,
                'count' => $item->count,
            ];
        });
    }

    public function getMonthlyTrend(int $year, ?string $department = null): Collection
    {
        $query = Expense::query()
            ->select(
                DB::raw('MONTH(expense_date) as month'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('expense_date', $year)
            ->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_PAID])
            ->groupBy(DB::raw('MONTH(expense_date)'))
            ->orderBy('month');

        if ($department) {
            $query->whereHas('user', fn($q) => $q->where('department', $department));
        }

        return $query->get();
    }

    public function getBudgetUtilization(int $year, int $month, ?string $department = null): Collection
    {
        $query = Budget::query()
            ->where('year', $year)
            ->where('month', $month)
            ->with('category');

        if ($department) {
            $query->where('department', $department);
        } else {
            $query->whereNull('department');
        }

        return $query->get()->map(function ($budget) {
            return [
                'category_id' => $budget->category_id,
                'category_name' => $budget->category->name ?? 'Unknown',
                'department' => $budget->department,
                'limit_amount' => $budget->limit_amount,
                'used_amount' => $budget->used_amount,
                'remaining_amount' => $budget->remaining_amount,
                'usage_percentage' => $budget->usage_percentage,
                'status' => $budget->status,
            ];
        });
    }

    public function getPaymentSummary(
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = Payment::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $payments = $query->get();

        return [
            'total_amount' => $payments->where('status', Payment::STATUS_COMPLETED)->sum('amount'),
            'total_count' => $payments->count(),
            'completed' => $payments->where('status', Payment::STATUS_COMPLETED)->count(),
            'pending' => $payments->where('status', Payment::STATUS_PENDING)->count(),
            'processing' => $payments->where('status', Payment::STATUS_PROCESSING)->count(),
            'failed' => $payments->where('status', Payment::STATUS_FAILED)->count(),
        ];
    }

    public function getDashboardStats(?User $user = null): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        if ($user && $user->isEmployee()) {
            return $this->getEmployeeDashboardStats($user);
        }

        if ($user && $user->isManager()) {
            return $this->getManagerDashboardStats($user);
        }

        if ($user && $user->isFinance()) {
            return $this->getFinanceDashboardStats();
        }

        return $this->getAdminDashboardStats();
    }

    protected function getEmployeeDashboardStats(User $user): array
    {
        $expenses = Expense::where('user_id', $user->id);

        return [
            'total_submitted' => (clone $expenses)->whereIn('status', [
                Expense::STATUS_SUBMITTED,
                Expense::STATUS_APPROVED,
                Expense::STATUS_PAID
            ])->count(),
            'pending_approval' => (clone $expenses)->where('status', Expense::STATUS_SUBMITTED)->count(),
            'approved' => (clone $expenses)->where('status', Expense::STATUS_APPROVED)->count(),
            'paid' => (clone $expenses)->where('status', Expense::STATUS_PAID)->count(),
            'rejected' => (clone $expenses)->where('status', Expense::STATUS_REJECTED)->count(),
            'total_amount_this_month' => (clone $expenses)
                ->whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_PAID])
                ->sum('amount'),
        ];
    }

    protected function getManagerDashboardStats(User $user): array
    {
        $subordinateIds = $user->getSubordinateIds();

        return [
            'pending_approval' => Expense::whereIn('user_id', $subordinateIds)
                ->where('status', Expense::STATUS_SUBMITTED)
                ->count(),
            'approved_this_month' => Expense::whereIn('user_id', $subordinateIds)
                ->where('status', Expense::STATUS_APPROVED)
                ->whereMonth('approved_at', now()->month)
                ->count(),
            'team_total_this_month' => Expense::whereIn('user_id', $subordinateIds)
                ->whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_PAID])
                ->sum('amount'),
            'team_members' => count($subordinateIds),
        ];
    }

    protected function getFinanceDashboardStats(): array
    {
        return [
            'pending_payment' => Expense::where('status', Expense::STATUS_APPROVED)->count(),
            'paid_this_month' => Payment::where('status', Payment::STATUS_COMPLETED)
                ->whereMonth('completed_at', now()->month)
                ->count(),
            'total_paid_this_month' => Payment::where('status', Payment::STATUS_COMPLETED)
                ->whereMonth('completed_at', now()->month)
                ->sum('amount'),
            'flagged_expenses' => Expense::where('is_flagged', true)->count(),
        ];
    }

    protected function getAdminDashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_expenses' => Expense::count(),
            'total_amount_this_month' => Expense::whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_PAID])
                ->sum('amount'),
            'pending_approval' => Expense::where('status', Expense::STATUS_SUBMITTED)->count(),
            'pending_payment' => Expense::where('status', Expense::STATUS_APPROVED)->count(),
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Approval;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    protected ExpenseService $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isManager() && !$user->isFinance() && !$user->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $query = Expense::with(['category', 'user', 'approvals.approver']);

        // Filter based on role
        if ($user->isAdmin()) {
            // Admin sees:
            // 1. Expenses needing admin approval (from Finance staff)
            // 2. Can also see all pending expenses
            $query->where(function($q) use ($user) {
                // Expenses with pending admin approval
                $q->whereHas('approvals', function($aq) {
                    $aq->where('stage', Approval::STAGE_ADMIN)
                       ->where('status', Approval::STATUS_PENDING);
                })
                // Or any other pending expenses (admin can take over)
                ->orWhereIn('status', [
                    Expense::STATUS_SUBMITTED, 
                    Expense::STATUS_MANAGER_APPROVED
                ]);
            });
        } elseif ($user->isFinance()) {
            // Finance sees manager-approved expenses OR manager expenses needing finance approval
            $query->where('status', Expense::STATUS_MANAGER_APPROVED)
                  ->orWhere(function($q) {
                      $q->where('status', Expense::STATUS_SUBMITTED)
                        ->whereHas('user.role', fn($r) => $r->where('slug', 'manager'));
                  });
        } elseif ($user->isManager()) {
            // Manager sees submitted expenses from their subordinates only
            $subordinateIds = $user->getSubordinateIds();
            $query->where('status', Expense::STATUS_SUBMITTED)
                  ->whereIn('user_id', $subordinateIds);
        }

        // Apply filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        }

        $expenses = $query->latest('submitted_at')->paginate(15)->withQueryString();
        $categories = \App\Models\ExpenseCategory::active()->get();

        return view('approvals.index', compact('expenses', 'categories'));
    }

    public function show(Expense $expense)
    {
        $this->authorizeApproval($expense);

        $expense->load(['category', 'user', 'approvals.approver']);
        
        // Get fraud warnings
        $fraudWarnings = $this->expenseService->checkForFraud($expense);

        return view('approvals.show', compact('expense', 'fraudWarnings'));
    }

    public function approve(Request $request, Expense $expense)
    {
        $this->authorizeApproval($expense);

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->expenseService->approve(
                $expense,
                $request->user(),
                $request->notes
            );

            return redirect()
                ->route('approvals.index')
                ->with('success', 'Expense approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, Expense $expense)
    {
        $this->authorizeApproval($expense);

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->expenseService->reject(
                $expense,
                $request->user(),
                $request->reason
            );

            return redirect()
                ->route('approvals.index')
                ->with('success', 'Expense rejected successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function history(Request $request)
    {
        $user = $request->user();

        $query = Approval::with(['expense.category', 'expense.user'])
            ->where('approver_id', $user->id)
            ->whereIn('status', [Approval::STATUS_APPROVED, Approval::STATUS_REJECTED]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('actioned_at', [$request->start_date, $request->end_date]);
        }

        $approvals = $query->latest('actioned_at')->paginate(15)->withQueryString();

        return view('approvals.history', compact('approvals'));
    }

    protected function authorizeApproval(Expense $expense): void
    {
        $user = auth()->user();

        // Admin can approve anything with pending approval
        if ($user->isAdmin()) {
            $hasPending = $expense->approvals()->where('status', Approval::STATUS_PENDING)->exists();
            if (!$hasPending && !in_array($expense->status, [Expense::STATUS_SUBMITTED, Expense::STATUS_MANAGER_APPROVED])) {
                abort(403, 'This expense has no pending approval.');
            }
            return;
        }

        // Finance can approve:
        // 1. Manager-approved expenses (from employees)
        // 2. Submitted expenses from managers (managers skip manager approval)
        if ($user->isFinance()) {
            $expenseOwner = $expense->user;
            
            // Manager's expense - goes directly to finance
            if ($expenseOwner->isManager() && $expense->status === Expense::STATUS_SUBMITTED) {
                return;
            }
            
            // Employee's expense - needs manager approval first
            if ($expense->status === Expense::STATUS_MANAGER_APPROVED) {
                return;
            }
            
            abort(403, 'Finance can only approve expenses that have been approved by manager, or direct submissions from managers.');
        }

        // Manager approval
        if (!$user->isManager()) {
            abort(403, 'Only managers, finance, or admin can approve expenses.');
        }

        if (!$expense->isSubmitted()) {
            abort(403, 'This expense is not pending approval.');
        }

        $subordinateIds = $user->getSubordinateIds();
        if (!in_array($expense->user_id, $subordinateIds)) {
            abort(403, 'You can only approve expenses from your subordinates.');
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Budget;
use App\Services\ExpenseService;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    protected ExpenseService $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Expense::with(['category', 'user']);

        // Filter by user role
        if ($user->isEmployee()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isManager()) {
            // Show own expenses and subordinates
            $subordinateIds = $user->getSubordinateIds();
            $query->where(function ($q) use ($user, $subordinateIds) {
                $q->where('user_id', $user->id)
                    ->orWhereIn('user_id', $subordinateIds);
            });
        }
        // Finance and Admin can see all

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('expense_number', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $expenses = $query->latest()->paginate(15)->withQueryString();
        $categories = ExpenseCategory::active()->get();
        $statuses = [
            Expense::STATUS_DRAFT,
            Expense::STATUS_SUBMITTED,
            Expense::STATUS_APPROVED,
            Expense::STATUS_REJECTED,
            Expense::STATUS_PAID,
        ];

        return view('expenses.index', compact('expenses', 'categories', 'statuses'));
    }

    public function create()
    {
        $categories = ExpenseCategory::active()->get();
        return view('expenses.create', compact('categories'));
    }

    public function store(StoreExpenseRequest $request)
    {
        try {
            $expense = $this->expenseService->create(
                $request->validated(),
                $request->user()
            );

            return redirect()
                ->route('expenses.show', $expense)
                ->with('success', 'Expense created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create expense: ' . $e->getMessage());
        }
    }

    public function show(Expense $expense)
    {
        $this->authorizeView($expense);

        $expense->load(['category', 'user', 'approvals.approver', 'payment.processor', 'flaggedByUser']);
        
        // Check for fraud warnings if user is finance
        $fraudWarnings = null;
        if (auth()->user()->isFinance() || auth()->user()->isAdmin()) {
            $fraudWarnings = $this->expenseService->checkForFraud($expense);
        }

        // Check budget status
        $budgetWarning = null;
        $budget = Budget::findForExpense($expense);
        if ($budget && $budget->isNearLimit()) {
            $budgetWarning = [
                'type' => $budget->status,
                'usage_percentage' => $budget->usage_percentage,
                'remaining' => $budget->remaining_amount,
            ];
        }

        return view('expenses.show', compact('expense', 'fraudWarnings', 'budgetWarning'));
    }

    public function edit(Expense $expense)
    {
        $this->authorizeEdit($expense);

        $categories = ExpenseCategory::active()->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $this->authorizeEdit($expense);

        $this->expenseService->update($expense, $request->validated());

        return redirect()
            ->route('expenses.show', $expense)
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $this->authorizeDelete($expense);

        $this->expenseService->delete($expense);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    public function submit(Expense $expense)
    {
        $this->authorizeSubmit($expense);

        try {
            $this->expenseService->submit($expense);
            return redirect()
                ->route('expenses.show', $expense)
                ->with('success', 'Expense submitted for approval.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function downloadReceipt(Expense $expense)
    {
        $this->authorizeView($expense);

        if (!$expense->receipt_path || !Storage::disk('public')->exists($expense->receipt_path)) {
            abort(404, 'Receipt not found.');
        }

        return Storage::disk('public')->download(
            $expense->receipt_path,
            $expense->receipt_original_name
        );
    }

    protected function authorizeView(Expense $expense): void
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isFinance()) {
            return;
        }

        if ($user->isManager()) {
            $subordinateIds = $user->getSubordinateIds();
            if ($expense->user_id === $user->id || in_array($expense->user_id, $subordinateIds)) {
                return;
            }
        }

        if ($expense->user_id === $user->id) {
            return;
        }

        abort(403, 'Unauthorized to view this expense.');
    }

    protected function authorizeEdit(Expense $expense): void
    {
        $user = auth()->user();

        if (!$expense->canBeEdited()) {
            abort(403, 'This expense cannot be edited.');
        }

        if ($expense->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'You can only edit your own expenses.');
        }
    }

    protected function authorizeDelete(Expense $expense): void
    {
        $user = auth()->user();

        if (!$expense->isDraft()) {
            abort(403, 'Only draft expenses can be deleted.');
        }

        if ($expense->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'You can only delete your own expenses.');
        }
    }

    protected function authorizeSubmit(Expense $expense): void
    {
        $user = auth()->user();

        if (!$expense->canBeSubmitted()) {
            abort(403, 'This expense cannot be submitted.');
        }

        if ($expense->user_id !== $user->id) {
            abort(403, 'You can only submit your own expenses.');
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = Budget::with('category');

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $query->where('year', $year)->where('month', $month);

        if ($request->filled('department')) {
            if ($request->department === 'global') {
                $query->whereNull('department');
            } else {
                $query->where('department', $request->department);
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $budgets = $query->latest()->paginate(15)->withQueryString();

        $categories = ExpenseCategory::active()->get();
        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        $years = range(now()->year - 1, now()->year + 1);
        $months = collect(range(1, 12))->mapWithKeys(fn($m) => [$m => date('F', mktime(0, 0, 0, $m, 1))]);

        return view('admin.budgets.index', compact('budgets', 'categories', 'departments', 'year', 'month', 'years', 'months'));
    }

    public function create()
    {
        $categories = ExpenseCategory::active()->get();
        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department');
        $years = range(now()->year - 1, now()->year + 1);
        $months = collect(range(1, 12))->mapWithKeys(fn($m) => [$m => date('F', mktime(0, 0, 0, $m, 1))]);

        return view('admin.budgets.create', compact('categories', 'departments', 'years', 'months'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'department' => 'nullable|string|max:100',
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'limit_amount' => 'required|numeric|min:0',
            'warning_threshold' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        // Check for duplicate
        $exists = Budget::where('category_id', $validated['category_id'])
            ->where('year', $validated['year'])
            ->where('month', $validated['month'])
            ->where('department', $validated['department'] ?? null)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'A budget already exists for this category, period, and department.');
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['used_amount'] = 0;

        $budget = Budget::create($validated);

        $this->auditService->logCreate(
            Budget::class,
            $budget->id,
            "Budget created for {$budget->category->name}",
            $budget->toArray()
        );

        return redirect()
            ->route('admin.budgets.index')
            ->with('success', 'Budget created successfully.');
    }

    public function edit(Budget $budget)
    {
        $categories = ExpenseCategory::active()->get();
        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department');
        $years = range(now()->year - 1, now()->year + 1);
        $months = collect(range(1, 12))->mapWithKeys(fn($m) => [$m => date('F', mktime(0, 0, 0, $m, 1))]);

        return view('admin.budgets.edit', compact('budget', 'categories', 'departments', 'years', 'months'));
    }

    public function update(Request $request, Budget $budget)
    {
        $validated = $request->validate([
            'limit_amount' => 'required|numeric|min:0',
            'warning_threshold' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $oldValues = $budget->toArray();

        $validated['is_active'] = $request->boolean('is_active', true);

        $budget->update($validated);

        $this->auditService->logUpdate(
            Budget::class,
            $budget->id,
            "Budget updated for {$budget->category->name}",
            $oldValues,
            $budget->toArray()
        );

        return redirect()
            ->route('admin.budgets.index')
            ->with('success', 'Budget updated successfully.');
    }

    public function destroy(Budget $budget)
    {
        $this->auditService->logDelete(
            Budget::class,
            $budget->id,
            "Budget deleted for {$budget->category->name}",
            $budget->toArray()
        );

        $budget->delete();

        return redirect()
            ->route('admin.budgets.index')
            ->with('success', 'Budget deleted successfully.');
    }

    public function copyFromPrevious(Request $request)
    {
        $validated = $request->validate([
            'source_year' => 'required|integer',
            'source_month' => 'required|integer|min:1|max:12',
            'target_year' => 'required|integer',
            'target_month' => 'required|integer|min:1|max:12',
        ]);

        $sourceBudgets = Budget::where('year', $validated['source_year'])
            ->where('month', $validated['source_month'])
            ->get();

        $copied = 0;
        foreach ($sourceBudgets as $source) {
            $exists = Budget::where('category_id', $source->category_id)
                ->where('year', $validated['target_year'])
                ->where('month', $validated['target_month'])
                ->where('department', $source->department)
                ->exists();

            if (!$exists) {
                Budget::create([
                    'category_id' => $source->category_id,
                    'department' => $source->department,
                    'year' => $validated['target_year'],
                    'month' => $validated['target_month'],
                    'limit_amount' => $source->limit_amount,
                    'used_amount' => 0,
                    'warning_threshold' => $source->warning_threshold,
                    'is_active' => true,
                ]);
                $copied++;
            }
        }

        return back()->with('success', "{$copied} budgets copied successfully.");
    }
}

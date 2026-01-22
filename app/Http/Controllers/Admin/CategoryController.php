<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = ExpenseCategory::withCount('expenses');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->latest()->paginate(15)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:expense_categories,code',
            'description' => 'nullable|string|max:1000',
            'default_limit' => 'required|numeric|min:0',
            'requires_receipt' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['requires_receipt'] = $request->boolean('requires_receipt', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        $category = ExpenseCategory::create($validated);

        $this->auditService->logCreate(
            ExpenseCategory::class,
            $category->id,
            "Expense category {$category->name} created",
            $category->toArray()
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(ExpenseCategory $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, ExpenseCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('expense_categories')->ignore($category->id)],
            'description' => 'nullable|string|max:1000',
            'default_limit' => 'required|numeric|min:0',
            'requires_receipt' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $oldValues = $category->toArray();

        $validated['requires_receipt'] = $request->boolean('requires_receipt', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        $category->update($validated);

        $this->auditService->logUpdate(
            ExpenseCategory::class,
            $category->id,
            "Expense category {$category->name} updated",
            $oldValues,
            $category->toArray()
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(ExpenseCategory $category)
    {
        if ($category->expenses()->exists()) {
            return back()->with('error', 'Cannot delete category with existing expenses.');
        }

        $this->auditService->logDelete(
            ExpenseCategory::class,
            $category->id,
            "Expense category {$category->name} deleted",
            $category->toArray()
        );

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function toggleStatus(ExpenseCategory $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();

        $status = $category->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Category {$status} successfully.");
    }
}

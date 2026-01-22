<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExpenseReportExport;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        // Default date range: current month
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $department = $request->get('department');
        $categoryId = $request->get('category_id');

        // Apply role-based filters
        if ($user->isEmployee()) {
            $summary = $this->reportService->getExpenseSummary($startDate, $endDate, $user->id);
            $byCategory = $this->reportService->getExpensesByCategory($startDate, $endDate)
                ->filter(fn($item) => \App\Models\Expense::where('user_id', $user->id)
                    ->where('category_id', $item['category_id'])
                    ->exists());
        } elseif ($user->isManager()) {
            $department = $department ?? $user->department;
            $summary = $this->reportService->getExpenseSummary($startDate, $endDate, null, $department);
            $byCategory = $this->reportService->getExpensesByCategory($startDate, $endDate, $department);
        } else {
            $summary = $this->reportService->getExpenseSummary($startDate, $endDate, null, $department, $categoryId);
            $byCategory = $this->reportService->getExpensesByCategory($startDate, $endDate, $department);
        }

        $byDepartment = $this->reportService->getExpensesByDepartment($startDate, $endDate);
        $monthlyTrend = $this->reportService->getMonthlyTrend(now()->year, $department);

        $categories = ExpenseCategory::active()->get();
        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        return view('reports.index', compact(
            'summary',
            'byCategory',
            'byDepartment',
            'monthlyTrend',
            'categories',
            'departments',
            'startDate',
            'endDate'
        ));
    }

    public function exportPdf(Request $request)
    {
        $this->authorizeExport();

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $department = $request->get('department');
        $status = $request->get('status');
        $categoryId = $request->get('category_id');

        // Get expenses for PDF
        $query = \App\Models\Expense::with(['user', 'category'])
            ->whereBetween('expense_date', [$startDate, $endDate]);

        if ($department) {
            $query->whereHas('user', fn($q) => $q->where('department', $department));
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();

        $summary = $this->reportService->getExpenseSummary($startDate, $endDate, null, $department);

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'department' => $department,
        ];

        $pdf = Pdf::loadView('reports.pdf.expenses', [
            'title' => 'Expense Report',
            'expenses' => $expenses,
            'summary' => $summary,
            'filters' => $filters,
            'generatedBy' => auth()->user()->name,
            'showSignatures' => $request->boolean('signatures'),
        ]);

        $filename = 'expense_report_' . $startDate . '_to_' . $endDate . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $this->authorizeExport();

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));
        $department = $request->get('department');

        $filename = 'expense_report_' . $startDate . '_to_' . $endDate . '.xlsx';

        return Excel::download(
            new ExpenseReportExport($startDate, $endDate, $department),
            $filename
        );
    }

    public function budgetUtilization(Request $request)
    {
        $this->authorizeReport();

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $department = $request->get('department');

        $budgets = $this->reportService->getBudgetUtilization($year, $month, $department);

        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        // Calculate summary
        $summary = [
            'total_budget' => $budgets->sum('limit_amount'),
            'total_used' => $budgets->sum('used_amount'),
            'total_remaining' => $budgets->sum('remaining_amount'),
        ];

        return view('reports.budget', compact('budgets', 'departments', 'year', 'month', 'summary'));
    }

    public function exportBudgetPdf(Request $request)
    {
        $this->authorizeExport();

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $department = $request->get('department');

        $budgets = $this->reportService->getBudgetUtilization($year, $month, $department);

        $totalBudget = $budgets->sum('budget_amount');
        $totalUsed = $budgets->sum('used_amount');
        $totalRemaining = $budgets->sum('remaining_amount');
        $usedPercentage = $totalBudget > 0 ? ($totalUsed / $totalBudget * 100) : 0;

        $pdf = Pdf::loadView('reports.pdf.budget', [
            'budgets' => $budgets,
            'totalBudget' => $totalBudget,
            'totalUsed' => $totalUsed,
            'totalRemaining' => $totalRemaining,
            'usedPercentage' => $usedPercentage,
            'period' => \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y'),
        ]);

        $filename = 'budget_report_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($filename);
    }

    public function paymentSummary(Request $request)
    {
        $this->authorizeReport();

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $summary = $this->reportService->getPaymentSummary($startDate, $endDate);

        return view('reports.payments', compact('summary', 'startDate', 'endDate'));
    }

    public function exportPaymentPdf(Request $request)
    {
        $this->authorizeExport();

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $payments = \App\Models\Payment::with(['expense.user', 'processedBy'])
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_transactions' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'success_count' => $payments->where('status', 'success')->count(),
            'failed_count' => $payments->where('status', 'failed')->count(),
        ];

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $pdf = Pdf::loadView('reports.pdf.payments', [
            'payments' => $payments,
            'summary' => $summary,
            'filters' => $filters,
        ]);

        $filename = 'payment_report_' . $startDate . '_to_' . $endDate . '.pdf';

        return $pdf->download($filename);
    }

    protected function authorizeReport(): void
    {
        $user = auth()->user();

        if (!$user->isFinance() && !$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Unauthorized to view reports.');
        }
    }

    protected function authorizeExport(): void
    {
        $user = auth()->user();

        if (!$user->isFinance() && !$user->isAdmin()) {
            abort(403, 'Unauthorized to export reports.');
        }
    }
}

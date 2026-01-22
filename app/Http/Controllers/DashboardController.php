<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $stats = $this->reportService->getDashboardStats($user);

        // Get recent expenses based on role
        if ($user->isEmployee()) {
            $recentExpenses = $user->expenses()
                ->with('category')
                ->latest()
                ->take(5)
                ->get();
        } elseif ($user->isManager()) {
            $subordinateIds = $user->getSubordinateIds();
            $recentExpenses = \App\Models\Expense::whereIn('user_id', $subordinateIds)
                ->with(['category', 'user'])
                ->where('status', \App\Models\Expense::STATUS_SUBMITTED)
                ->latest()
                ->take(5)
                ->get();
        } elseif ($user->isFinance()) {
            $recentExpenses = \App\Models\Expense::with(['category', 'user'])
                ->where('status', \App\Models\Expense::STATUS_APPROVED)
                ->latest()
                ->take(5)
                ->get();
        } else {
            $recentExpenses = \App\Models\Expense::with(['category', 'user'])
                ->latest()
                ->take(10)
                ->get();
        }

        // Get monthly chart data
        $monthlyData = $this->reportService->getMonthlyTrend(now()->year);

        return view('dashboard', compact('stats', 'recentExpenses', 'monthlyData'));
    }
}

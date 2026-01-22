<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BudgetController;
use App\Http\Controllers\Admin\AuditLogController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'active'])
    ->name('dashboard');

// Authenticated routes
Route::middleware(['auth', 'active'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Expenses
    Route::resource('expenses', ExpenseController::class);
    Route::post('/expenses/{expense}/submit', [ExpenseController::class, 'submit'])->name('expenses.submit');
    Route::get('/expenses/{expense}/receipt', [ExpenseController::class, 'downloadReceipt'])->name('expenses.receipt');

    // Approvals (Manager, Finance, Admin)
    Route::prefix('approvals')->name('approvals.')->middleware('role:manager,finance,admin')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::get('/{expense}', [ApprovalController::class, 'show'])->name('show');
        Route::post('/{expense}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{expense}/reject', [ApprovalController::class, 'reject'])->name('reject');
        Route::get('/history/list', [ApprovalController::class, 'history'])->name('history');
    });

    // Payments (Finance)
    Route::prefix('payments')->name('payments.')->middleware('role:finance,admin')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/flagged', [PaymentController::class, 'flagged'])->name('flagged');
        Route::get('/{expense}', [PaymentController::class, 'show'])->name('show');
        Route::post('/{expense}/process', [PaymentController::class, 'process'])->name('process');
        Route::post('/bulk-process', [PaymentController::class, 'bulkProcess'])->name('bulk-process');
        Route::post('/{payment}/retry', [PaymentController::class, 'retry'])->name('retry');
        Route::post('/{expense}/flag', [PaymentController::class, 'flag'])->name('flag');
        Route::post('/{expense}/unflag', [PaymentController::class, 'unflag'])->name('unflag');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf')->middleware('role:finance,admin');
        Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel')->middleware('role:finance,admin');
        Route::get('/budget', [ReportController::class, 'budgetUtilization'])->name('budget')->middleware('role:finance,admin,manager');
        Route::get('/budget/export/pdf', [ReportController::class, 'exportBudgetPdf'])->name('budget.export.pdf')->middleware('role:finance,admin');
        Route::get('/payments', [ReportController::class, 'paymentSummary'])->name('payments')->middleware('role:finance,admin');
        Route::get('/payments/export/pdf', [ReportController::class, 'exportPaymentPdf'])->name('payments.export.pdf')->middleware('role:finance,admin');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        // User Management
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Category Management
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::post('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

        // Budget Management
        Route::resource('budgets', BudgetController::class)->except(['show']);
        Route::post('/budgets/copy', [BudgetController::class, 'copyFromPrevious'])->name('budgets.copy');

        // Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });
});

require __DIR__.'/auth.php';

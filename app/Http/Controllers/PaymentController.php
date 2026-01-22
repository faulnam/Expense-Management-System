<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected ExpenseService $expenseService;

    public function __construct(PaymentService $paymentService, ExpenseService $expenseService)
    {
        $this->paymentService = $paymentService;
        $this->expenseService = $expenseService;
    }

    public function index(Request $request)
    {
        $this->authorizeFinance();

        // Pending payments (approved expenses)
        $pendingQuery = Expense::with(['category', 'user'])
            ->where('status', Expense::STATUS_APPROVED);

        if ($request->filled('category_id')) {
            $pendingQuery->where('category_id', $request->category_id);
        }

        $pendingExpenses = $pendingQuery->latest('approved_at')->paginate(10, ['*'], 'pending');

        // Payment history
        $paymentsQuery = Payment::with(['expense.category', 'expense.user', 'processor']);

        if ($request->filled('payment_status')) {
            $paymentsQuery->where('status', $request->payment_status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $paymentsQuery->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $payments = $paymentsQuery->latest()->paginate(10, ['*'], 'payments');

        $categories = \App\Models\ExpenseCategory::active()->get();

        return view('payments.index', compact('pendingExpenses', 'payments', 'categories'));
    }

    public function show(Expense $expense)
    {
        $this->authorizeFinance();

        $expense->load(['category', 'user', 'approvals.approver', 'payment']);

        // Get fraud warnings
        $fraudWarnings = $this->expenseService->checkForFraud($expense);

        return view('payments.show', compact('expense', 'fraudWarnings'));
    }

    public function process(Request $request, Expense $expense)
    {
        $this->authorizeFinance();

        if (!$expense->canBePaid()) {
            return back()->with('error', 'This expense is not eligible for payment.');
        }

        $request->validate([
            'payment_method' => 'required|in:bank_transfer,cash,check',
            'bank_name' => 'required_if:payment_method,bank_transfer|nullable|string',
            'account_number' => 'required_if:payment_method,bank_transfer|nullable|string',
            'account_name' => 'required_if:payment_method,bank_transfer|nullable|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $payment = $this->paymentService->processPayment(
                $expense,
                $request->user(),
                $request->only(['payment_method', 'bank_name', 'account_number', 'account_name', 'notes'])
            );

            if ($payment->isCompleted()) {
                return redirect()
                    ->route('payments.index')
                    ->with('success', 'Payment processed successfully.');
            } else {
                return redirect()
                    ->route('payments.index')
                    ->with('warning', 'Payment processing failed. Please retry.');
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function retry(Payment $payment)
    {
        $this->authorizeFinance();

        try {
            $payment = $this->paymentService->retryPayment($payment);

            if ($payment->isCompleted()) {
                return redirect()
                    ->route('payments.index')
                    ->with('success', 'Payment retry successful.');
            } else {
                return redirect()
                    ->route('payments.index')
                    ->with('warning', 'Payment retry failed.');
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function flag(Request $request, Expense $expense)
    {
        $this->authorizeFinance();

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $this->expenseService->flag($expense, $request->user(), $request->reason);

        return back()->with('success', 'Expense flagged for review.');
    }

    public function unflag(Expense $expense)
    {
        $this->authorizeFinance();

        $this->expenseService->unflag($expense);

        return back()->with('success', 'Flag removed from expense.');
    }

    public function flagged(Request $request)
    {
        $this->authorizeFinance();

        $expenses = Expense::with(['category', 'user', 'flaggedByUser'])
            ->where('is_flagged', true)
            ->latest('flagged_at')
            ->paginate(15);

        return view('payments.flagged', compact('expenses'));
    }

    protected function authorizeFinance(): void
    {
        $user = auth()->user();

        if (!$user->isFinance() && !$user->isAdmin()) {
            abort(403, 'Only finance staff can access this page.');
        }
    }
}

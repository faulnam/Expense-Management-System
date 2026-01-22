<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Expense;
use App\Services\PakasirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PakasirWebhookController extends Controller
{
    protected PakasirService $pakasirService;

    public function __construct(PakasirService $pakasirService)
    {
        $this->pakasirService = $pakasirService;
    }

    /**
     * Handle incoming webhook from Pak Kasir
     *
     * Webhook payload:
     * {
     *   "amount": 22000,
     *   "order_id": "PAY-240910HDE7C9",
     *   "project": "your-project",
     *   "status": "completed",
     *   "payment_method": "qris",
     *   "completed_at": "2024-09-10T08:07:02.819+07:00"
     * }
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Pakasir webhook received', $payload);

        // Validate webhook
        if (!$this->pakasirService->validateWebhook($payload)) {
            Log::warning('Invalid Pakasir webhook payload', $payload);
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Only process completed payments
        if ($payload['status'] !== 'completed') {
            Log::info('Pakasir webhook: Payment not completed', ['status' => $payload['status']]);
            return response()->json(['message' => 'Ignored non-completed status']);
        }

        // Find payment by payment_number (order_id)
        $payment = Payment::where('payment_number', $payload['order_id'])->first();

        if (!$payment) {
            Log::warning('Payment not found for webhook', ['order_id' => $payload['order_id']]);
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Validate amount matches
        if ((int) $payment->amount !== (int) $payload['amount']) {
            Log::warning('Payment amount mismatch', [
                'expected' => $payment->amount,
                'received' => $payload['amount'],
            ]);
            return response()->json(['error' => 'Amount mismatch'], 400);
        }

        // Double-check with API (recommended by Pak Kasir docs)
        $verification = $this->pakasirService->getTransactionDetail(
            $payload['order_id'],
            (int) $payload['amount']
        );

        if (!$verification['success'] || 
            ($verification['data']['transaction']['status'] ?? '') !== 'completed') {
            Log::warning('Payment verification failed', $verification);
            return response()->json(['error' => 'Verification failed'], 400);
        }

        // Update payment status
        $payment->update([
            'status' => Payment::STATUS_COMPLETED,
            'paid_at' => now(),
            'gateway_response' => $payload,
            'payment_method' => $payload['payment_method'] ?? $payment->payment_method,
        ]);

        // Update expense status to paid
        if ($payment->expense) {
            $payment->expense->update([
                'status' => Expense::STATUS_PAID,
                'paid_at' => now(),
            ]);
        }

        Log::info('Payment completed via Pakasir webhook', [
            'payment_id' => $payment->id,
            'order_id' => $payload['order_id'],
        ]);

        return response()->json(['message' => 'Payment processed successfully']);
    }
}

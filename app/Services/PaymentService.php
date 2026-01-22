<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected AuditService $auditService;
    protected PakasirService $pakasirService;

    public function __construct(AuditService $auditService, PakasirService $pakasirService)
    {
        $this->auditService = $auditService;
        $this->pakasirService = $pakasirService;
    }

    public function processPayment(Expense $expense, User $processor, array $paymentData = []): Payment
    {
        return DB::transaction(function () use ($expense, $processor, $paymentData) {
            if (!$expense->canBePaid()) {
                throw new \Exception('Expense is not eligible for payment.');
            }

            $paymentMethod = $paymentData['payment_method'] ?? 'bank_transfer';

            // Create payment record
            $payment = Payment::create([
                'expense_id' => $expense->id,
                'processed_by' => $processor->id,
                'amount' => $expense->amount,
                'payment_method' => $paymentMethod,
                'bank_name' => $paymentData['bank_name'] ?? null,
                'account_number' => $paymentData['account_number'] ?? null,
                'account_name' => $paymentData['account_name'] ?? null,
                'status' => Payment::STATUS_PROCESSING,
                'processed_at' => now(),
                'notes' => $paymentData['notes'] ?? null,
            ]);

            // For online payment methods (QRIS, VA), create transaction via Pak Kasir
            if (in_array($paymentMethod, ['qris', 'bni_va', 'bri_va', 'cimb_niaga_va', 'permata_va', 'maybank_va', 'sampoerna_va', 'bnc_va', 'artha_graha_va', 'atm_bersama_va', 'paypal'])) {
                $gatewayResult = $this->processOnlinePayment($payment, $paymentMethod);
            } else {
                // For manual methods (cash, check, bank_transfer), mark as completed immediately
                $gatewayResult = $this->processManualPayment($payment);
            }

            $payment->gateway_reference = $gatewayResult['reference'] ?? null;
            $payment->gateway_status = $gatewayResult['status'];
            $payment->gateway_response = $gatewayResult;

            if ($gatewayResult['status'] === 'success' || $gatewayResult['status'] === 'pending') {
                if ($gatewayResult['status'] === 'success') {
                    $payment->status = Payment::STATUS_COMPLETED;
                    $payment->completed_at = now();

                    // Update expense status
                    $expense->status = Expense::STATUS_PAID;
                    $expense->paid_at = now();
                    $expense->save();
                } else {
                    // Pending - waiting for payment confirmation via webhook
                    $payment->status = Payment::STATUS_PENDING;
                }
            } else {
                $payment->status = Payment::STATUS_FAILED;
            }

            $payment->save();

            $this->auditService->logPayment(
                Expense::class,
                $expense->id,
                "Payment {$payment->payment_number} processed for expense {$expense->expense_number}",
                [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'status' => $payment->status,
                    'gateway_reference' => $payment->gateway_reference,
                ]
            );

            return $payment;
        });
    }

    /**
     * Process online payment via Pak Kasir
     */
    protected function processOnlinePayment(Payment $payment, string $method): array
    {
        if (!$this->pakasirService->isConfigured()) {
            Log::warning('Pakasir not configured, falling back to simulation');
            return $this->simulatePaymentGateway($payment);
        }

        try {
            $result = $this->pakasirService->createTransaction(
                $payment->payment_number,
                (int) $payment->amount,
                $method
            );

            if ($result['success']) {
                $paymentData = $result['data']['payment'] ?? [];
                
                return [
                    'status' => 'pending',
                    'reference' => $payment->payment_number,
                    'message' => 'Transaction created, waiting for payment',
                    'timestamp' => now()->toIso8601String(),
                    'payment_number' => $paymentData['payment_number'] ?? null,
                    'total_payment' => $paymentData['total_payment'] ?? $payment->amount,
                    'expired_at' => $paymentData['expired_at'] ?? null,
                    'qr_string' => $paymentData['payment_number'] ?? null,
                ];
            }

            return [
                'status' => 'failed',
                'reference' => null,
                'message' => $result['message'] ?? 'Failed to create transaction',
                'timestamp' => now()->toIso8601String(),
                'error_code' => 'PAKASIR_ERROR',
            ];
        } catch (\Exception $e) {
            Log::error('Pakasir payment error: ' . $e->getMessage());
            
            return [
                'status' => 'failed',
                'reference' => null,
                'message' => 'Payment gateway error: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
                'error_code' => 'GATEWAY_ERROR',
            ];
        }
    }

    /**
     * Process manual payment (cash, check, bank_transfer)
     */
    protected function processManualPayment(Payment $payment): array
    {
        return [
            'status' => 'success',
            'reference' => 'MAN' . strtoupper(uniqid()),
            'message' => 'Manual payment processed successfully',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Simulate payment gateway for testing
     */
    protected function simulatePaymentGateway(Payment $payment): array
    {
        // Simulated success response for testing
        $success = rand(1, 100) <= 95; // 95% success rate for simulation

        if ($success) {
            return [
                'status' => 'success',
                'reference' => 'SIM' . strtoupper(uniqid()),
                'message' => 'Payment processed successfully (simulation)',
                'timestamp' => now()->toIso8601String(),
            ];
        } else {
            return [
                'status' => 'failed',
                'reference' => null,
                'message' => 'Payment failed - simulation error',
                'timestamp' => now()->toIso8601String(),
                'error_code' => 'SIMULATION_FAILED',
            ];
        }
    }

    public function retryPayment(Payment $payment): Payment
    {
        if (!$payment->isFailed()) {
            throw new \Exception('Only failed payments can be retried.');
        }

        $payment->status = Payment::STATUS_PROCESSING;
        $payment->processed_at = now();
        $payment->save();

        $paymentMethod = $payment->payment_method;

        // Retry based on payment method
        if (in_array($paymentMethod, ['qris', 'bni_va', 'bri_va', 'cimb_niaga_va', 'permata_va', 'maybank_va', 'sampoerna_va', 'bnc_va', 'artha_graha_va', 'atm_bersama_va', 'paypal'])) {
            $gatewayResult = $this->processOnlinePayment($payment, $paymentMethod);
        } else {
            $gatewayResult = $this->processManualPayment($payment);
        }

        $payment->gateway_reference = $gatewayResult['reference'] ?? null;
        $payment->gateway_status = $gatewayResult['status'];
        $payment->gateway_response = $gatewayResult;

        if ($gatewayResult['status'] === 'success') {
            $payment->status = Payment::STATUS_COMPLETED;
            $payment->completed_at = now();

            $expense = $payment->expense;
            $expense->status = Expense::STATUS_PAID;
            $expense->paid_at = now();
            $expense->save();
        } elseif ($gatewayResult['status'] === 'pending') {
            $payment->status = Payment::STATUS_PENDING;
        } else {
            $payment->status = Payment::STATUS_FAILED;
        }

        $payment->save();

        return $payment;
    }

    /**
     * Get payment URL for online payment redirect
     */
    public function getPaymentUrl(Payment $payment, array $options = []): ?string
    {
        if (!$this->pakasirService->isConfigured()) {
            return null;
        }

        return $this->pakasirService->generatePaymentUrl(
            $payment->payment_number,
            (int) $payment->amount,
            $options
        );
    }

    public function getPaymentHistory(Expense $expense): array
    {
        return $expense->payment()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}

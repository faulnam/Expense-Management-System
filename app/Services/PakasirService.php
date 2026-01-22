<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PakasirService
{
    protected string $baseUrl;
    protected string $projectSlug;
    protected string $apiKey;
    protected string $mode;

    public function __construct()
    {
        $this->baseUrl = config('pakasir.base_url', 'https://app.pakasir.com');
        $this->projectSlug = config('pakasir.project_slug');
        $this->apiKey = config('pakasir.api_key');
        $this->mode = config('pakasir.mode', 'sandbox');
    }

    /**
     * Create a new transaction
     *
     * @param string $orderId
     * @param int $amount Amount in IDR (tanpa titik/koma)
     * @param string $paymentMethod qris, bni_va, bri_va, dll
     * @return array
     */
    public function createTransaction(string $orderId, int $amount, string $paymentMethod = 'qris'): array
    {
        $url = "{$this->baseUrl}/api/transactioncreate/{$paymentMethod}";

        try {
            $response = Http::post($url, [
                'project' => $this->projectSlug,
                'order_id' => $orderId,
                'amount' => $amount,
                'api_key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Pakasir create transaction failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create transaction',
                'error' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Pakasir API error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get transaction detail
     *
     * @param string $orderId
     * @param int $amount
     * @return array
     */
    public function getTransactionDetail(string $orderId, int $amount): array
    {
        $url = "{$this->baseUrl}/api/transactiondetail";

        try {
            $response = Http::get($url, [
                'project' => $this->projectSlug,
                'order_id' => $orderId,
                'amount' => $amount,
                'api_key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get transaction detail',
            ];
        } catch (\Exception $e) {
            Log::error('Pakasir API error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel a transaction
     *
     * @param string $orderId
     * @param int $amount
     * @return array
     */
    public function cancelTransaction(string $orderId, int $amount): array
    {
        $url = "{$this->baseUrl}/api/transactioncancel";

        try {
            $response = Http::post($url, [
                'project' => $this->projectSlug,
                'order_id' => $orderId,
                'amount' => $amount,
                'api_key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to cancel transaction',
            ];
        } catch (\Exception $e) {
            Log::error('Pakasir API error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Simulate payment (only works in sandbox mode)
     *
     * @param string $orderId
     * @param int $amount
     * @return array
     */
    public function simulatePayment(string $orderId, int $amount): array
    {
        if ($this->mode !== 'sandbox') {
            return [
                'success' => false,
                'message' => 'Payment simulation only available in sandbox mode',
            ];
        }

        $url = "{$this->baseUrl}/api/paymentsimulation";

        try {
            $response = Http::post($url, [
                'project' => $this->projectSlug,
                'order_id' => $orderId,
                'amount' => $amount,
                'api_key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to simulate payment',
            ];
        } catch (\Exception $e) {
            Log::error('Pakasir API error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate payment URL for redirect method
     *
     * @param string $orderId
     * @param int $amount
     * @param array $options ['redirect' => 'url', 'qris_only' => true/false]
     * @return string
     */
    public function generatePaymentUrl(string $orderId, int $amount, array $options = []): string
    {
        $url = "{$this->baseUrl}/pay/{$this->projectSlug}/{$amount}?order_id={$orderId}";

        if (!empty($options['redirect'])) {
            $url .= '&redirect=' . urlencode($options['redirect']);
        }

        if (!empty($options['qris_only'])) {
            $url .= '&qris_only=1';
        }

        return $url;
    }

    /**
     * Generate PayPal payment URL
     *
     * @param string $orderId
     * @param int $amount Amount in IDR (akan dikonversi ke USD dengan kurs Rp 15.000/USD)
     * @return string
     */
    public function generatePaypalUrl(string $orderId, int $amount): string
    {
        return "{$this->baseUrl}/paypal/{$this->projectSlug}/{$amount}?order_id={$orderId}";
    }

    /**
     * Validate webhook payload
     *
     * @param array $payload
     * @return bool
     */
    public function validateWebhook(array $payload): bool
    {
        // Validate required fields
        $requiredFields = ['amount', 'order_id', 'project', 'status'];
        
        foreach ($requiredFields as $field) {
            if (!isset($payload[$field])) {
                return false;
            }
        }

        // Validate project matches
        if ($payload['project'] !== $this->projectSlug) {
            return false;
        }

        return true;
    }

    /**
     * Get available payment methods
     *
     * @return array
     */
    public function getPaymentMethods(): array
    {
        return config('pakasir.payment_methods', []);
    }

    /**
     * Check if configuration is valid
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->projectSlug) && !empty($this->apiKey);
    }
}

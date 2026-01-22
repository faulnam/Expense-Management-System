# Pak Kasir Payment Gateway Integration

## Overview

EMS (Expense Management System) terintegrasi dengan **Pak Kasir** (https://pakasir.com) sebagai payment gateway untuk pemrosesan pembayaran online menggunakan QRIS dan Virtual Account.

## Supported Payment Methods

### Online Payment (via Pak Kasir)
- **QRIS** - Pembayaran QR Code (All Banks)
- **Virtual Account**:
  - BNI VA
  - BRI VA
  - CIMB Niaga VA
  - Permata VA
  - Maybank VA
  - Bank Sampoerna VA
  - Bank Neo Commerce VA
  - Artha Graha VA
  - ATM Bersama VA
- **PayPal** - Pembayaran internasional

### Manual Payment
- Bank Transfer (Manual)
- Cash
- Check

## Setup Instructions

### 1. Register at Pak Kasir

1. Buka https://app.pakasir.com/ dan daftar akun baru
2. Login dan buat Project baru
3. Catat **Project Slug** dan **API Key** dari dashboard

### 2. Configure .env

Tambahkan konfigurasi berikut ke file `.env`:

```env
PAKASIR_PROJECT_SLUG=your-project-slug
PAKASIR_API_KEY=your-api-key
PAKASIR_MODE=sandbox    # atau 'production' untuk live
PAKASIR_DEFAULT_METHOD=qris
PAKASIR_REDIRECT_URL=https://your-domain.com/payments/complete
```

### 3. Setup Webhook

Di dashboard Pak Kasir, set Webhook URL ke:
```
https://your-domain.com/api/pakasir/webhook
```

Webhook akan menerima notifikasi saat:
- Pembayaran berhasil
- Pembayaran expired
- Status transaksi berubah

## Architecture

### Files Structure

```
app/
├── Services/
│   ├── PakasirService.php      # API client for Pak Kasir
│   └── PaymentService.php      # Payment processing logic
├── Http/Controllers/
│   └── Api/
│       └── PakasirWebhookController.php  # Webhook handler
config/
└── pakasir.php                 # Configuration file
routes/
└── api.php                     # API routes including webhook
```

### Flow Diagram

```
[User] → [Process Payment] → [PaymentService]
                                    ↓
                    [Online Method?] ─ Yes → [PakasirService.createTransaction()]
                            │                           ↓
                           No              [Pak Kasir API] → [Return Payment Details]
                            │                           ↓
                            ↓              [Payment Status: PENDING]
                [processManualPayment()]
                            ↓
                [Payment Status: COMPLETED]
                
                
[Pak Kasir] → [Webhook POST] → [PakasirWebhookController]
                                        ↓
                              [Verify with API]
                                        ↓
                              [Update Payment & Expense Status]
```

## API Reference

### PakasirService Methods

```php
// Create new transaction
$result = $pakasirService->createTransaction(
    orderId: 'PAY-001',
    amount: 150000,
    method: 'qris'  // or 'bni_va', 'bri_va', etc.
);

// Get transaction detail
$result = $pakasirService->getTransactionDetail('PAY-001');

// Cancel transaction
$result = $pakasirService->cancelTransaction('PAY-001');

// Simulate payment (sandbox only)
$result = $pakasirService->simulatePayment('PAY-001');

// Generate redirect URL
$url = $pakasirService->generatePaymentUrl('PAY-001', 150000);

// Validate webhook payload
$isValid = $pakasirService->validateWebhook($payload);
```

### Webhook Payload

Pak Kasir mengirim webhook dengan format:

```json
{
    "amount": 150000,
    "order_id": "PAY-001",
    "project": "your-project-slug",
    "status": "paid",
    "payment_method": "qris",
    "completed_at": "2024-01-15 14:30:00"
}
```

## Testing

### Sandbox Mode

Untuk testing, set `PAKASIR_MODE=sandbox` di `.env`.

Simulasi pembayaran dapat dilakukan dengan:

```php
$pakasirService->simulatePayment('PAY-001');
```

Atau via API langsung:
```bash
curl -X POST https://app.pakasir.com/api/paymentsimulation \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -d "project=your-project-slug&order_id=PAY-001"
```

### Production Mode

Set `PAKASIR_MODE=production` di `.env` untuk mode live.

## Troubleshooting

### Payment Not Created
- Pastikan API Key dan Project Slug sudah benar
- Cek log di `storage/logs/laravel.log`
- Pastikan amount minimal sesuai ketentuan (biasanya min 1000)

### Webhook Not Received
- Pastikan URL webhook bisa diakses publik
- Cek CSRF exception untuk route webhook
- Verifikasi project slug sama dengan yang di `.env`

### Payment Status Not Updated
- Cek webhook log di dashboard Pak Kasir
- Verifikasi order_id match dengan payment_number di database
- Pastikan Payment model ada method `isCompleted()`, `isPending()`, dll

## Security

- API Key disimpan di `.env`, JANGAN commit ke repository
- Webhook divalidasi dengan verifikasi ke API Pak Kasir
- CSRF protection disabled untuk route webhook (sudah dihandle di verifycsrf middleware)

## Support

- Dokumentasi Pak Kasir: https://pakasir.com/p/docs
- Support Pak Kasir: [Contact via website]

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pakasir Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Dokumentasi: https://pakasir.com/p/docs
    | Daftar/Login: https://app.pakasir.com/
    |
    */

    // Project slug dari Pak Kasir
    'project_slug' => env('PAKASIR_PROJECT_SLUG', ''),

    // API Key dari halaman detail proyek
    'api_key' => env('PAKASIR_API_KEY', ''),

    // Mode: sandbox atau production
    'mode' => env('PAKASIR_MODE', 'sandbox'),

    // Base URL API Pak Kasir
    'base_url' => 'https://app.pakasir.com',

    // Default payment method
    'default_method' => env('PAKASIR_DEFAULT_METHOD', 'qris'),

    // Custom redirect URL setelah pembayaran
    'redirect_url' => env('PAKASIR_REDIRECT_URL', ''),

    // Available payment methods
    'payment_methods' => [
        'qris' => 'QRIS (Semua E-Wallet & M-Banking)',
        'bni_va' => 'BNI Virtual Account',
        'bri_va' => 'BRI Virtual Account',
        'cimb_niaga_va' => 'CIMB Niaga Virtual Account',
        'permata_va' => 'Permata Virtual Account',
        'maybank_va' => 'Maybank Virtual Account',
        'sampoerna_va' => 'Bank Sampoerna Virtual Account',
        'bnc_va' => 'Bank Neo Commerce Virtual Account',
        'artha_graha_va' => 'Artha Graha Virtual Account',
        'atm_bersama_va' => 'ATM Bersama Virtual Account',
        'paypal' => 'PayPal',
    ],

    // Webhook secret untuk validasi (opsional, untuk keamanan tambahan)
    'webhook_secret' => env('PAKASIR_WEBHOOK_SECRET', ''),
];

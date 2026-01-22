<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PakasirWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Pakasir Payment Gateway Webhook
// URL: {APP_URL}/api/pakasir/webhook
// Daftarkan URL ini di form Edit Proyek Pak Kasir
Route::post('/pakasir/webhook', [PakasirWebhookController::class, 'handle'])
    ->name('pakasir.webhook');

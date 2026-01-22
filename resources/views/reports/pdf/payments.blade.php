<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
        }
        
        .container {
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #8b5cf6;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            color: #7c3aed;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #6b7280;
            font-size: 12px;
        }
        
        .report-title {
            margin-bottom: 20px;
        }
        
        .report-title h2 {
            font-size: 18px;
            color: #111827;
        }
        
        .report-title p {
            color: #6b7280;
            font-size: 10px;
        }
        
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        
        .summary-card .number {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
        }
        
        .summary-card .label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .data-table th {
            background-color: #7c3aed;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .data-table .text-right {
            text-align: right;
        }
        
        .data-table .text-center {
            text-align: center;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .status-badge.success {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-badge.pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-badge.failed {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .totals-row {
            background-color: #7c3aed !important;
            color: white;
            font-weight: bold;
        }
        
        .totals-row td {
            border-bottom: none;
            padding: 12px 8px;
        }
        
        .payment-method {
            display: inline-block;
            padding: 2px 6px;
            background-color: #ede9fe;
            color: #5b21b6;
            border-radius: 4px;
            font-size: 9px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name', 'EMS') }}</h1>
            <p>Expense Management System</p>
        </div>
        
        <div class="report-title">
            <h2>Payment Transaction Report</h2>
            <p>Generated on: {{ now()->format('d M Y, H:i') }}</p>
            @if(isset($filters['start_date']) && isset($filters['end_date']))
            <p>Period: {{ $filters['start_date'] }} - {{ $filters['end_date'] }}</p>
            @endif
        </div>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="number">{{ $summary['total_transactions'] ?? 0 }}</div>
                <div class="label">Total Transactions</div>
            </div>
            <div class="summary-card">
                <div class="number">Rp {{ number_format($summary['total_amount'] ?? 0, 0, ',', '.') }}</div>
                <div class="label">Total Amount</div>
            </div>
            <div class="summary-card">
                <div class="number">{{ $summary['success_count'] ?? 0 }}</div>
                <div class="label">Successful</div>
            </div>
            <div class="summary-card">
                <div class="number">{{ $summary['failed_count'] ?? 0 }}</div>
                <div class="label">Failed</div>
            </div>
        </div>
        
        <!-- Payments Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 12%">Date</th>
                    <th style="width: 15%">Transaction ID</th>
                    <th style="width: 15%">Employee</th>
                    <th style="width: 15%" class="text-right">Amount</th>
                    <th style="width: 12%">Method</th>
                    <th style="width: 10%" class="text-center">Status</th>
                    <th style="width: 16%">Processed By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $index => $payment)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $payment->transaction_id ?? '-' }}</td>
                    <td>{{ $payment->expense->user->name ?? 'N/A' }}</td>
                    <td class="text-right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    <td>
                        <span class="payment-method">{{ strtoupper($payment->payment_method ?? 'Transfer') }}</span>
                    </td>
                    <td class="text-center">
                        <span class="status-badge {{ $payment->status == 'success' ? 'success' : ($payment->status == 'pending' ? 'pending' : 'failed') }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td>{{ $payment->processedBy->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No payments found</td>
                </tr>
                @endforelse
                
                @if(isset($payments) && count($payments) > 0)
                <tr class="totals-row">
                    <td colspan="4" class="text-right">TOTAL:</td>
                    <td class="text-right">Rp {{ number_format($payments->sum('amount'), 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                </tr>
                @endif
            </tbody>
        </table>
        
        <div class="footer">
            <p>Payment Gateway: Pak Kasir (Sandbox Mode)</p>
            <p>{{ config('app.name', 'EMS') }} - Expense Management System &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>

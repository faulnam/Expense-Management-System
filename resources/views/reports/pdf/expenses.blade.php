<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Expense Report' }}</title>
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
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #6b7280;
            font-size: 12px;
        }
        
        /* Company Info */
        .company-info {
            margin-bottom: 20px;
        }
        
        .company-info h2 {
            font-size: 18px;
            color: #111827;
        }
        
        .company-info p {
            color: #6b7280;
            font-size: 10px;
        }
        
        /* Report Meta */
        .report-meta {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .report-meta table {
            width: 100%;
        }
        
        .report-meta td {
            padding: 3px 0;
        }
        
        .report-meta .label {
            font-weight: bold;
            color: #374151;
            width: 150px;
        }
        
        .report-meta .value {
            color: #6b7280;
        }
        
        /* Summary Box */
        .summary-box {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        
        .summary-item .number {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
        }
        
        .summary-item .label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
        }
        
        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .data-table th {
            background-color: #1e40af;
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
        
        /* Status Badges */
        .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-draft {
            background-color: #e5e7eb;
            color: #374151;
        }
        
        .status-submitted {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-approved,
        .status-manager_approved,
        .status-finance_approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .status-paid {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        /* Totals Row */
        .totals-row {
            background-color: #1e40af !important;
            color: white;
            font-weight: bold;
        }
        
        .totals-row td {
            border-bottom: none;
            padding: 12px 8px;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
        
        .page-number {
            text-align: right;
            font-size: 9px;
            color: #9ca3af;
        }
        
        /* Signatures Section */
        .signatures {
            margin-top: 50px;
            display: table;
            width: 100%;
        }
        
        .signature-box {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 0 20px;
        }
        
        .signature-line {
            border-top: 1px solid #374151;
            margin-top: 60px;
            padding-top: 10px;
        }
        
        .signature-label {
            font-size: 10px;
            color: #6b7280;
        }
        
        .signature-name {
            font-size: 11px;
            font-weight: bold;
            color: #111827;
        }
        
        /* Watermark for flagged */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(239, 68, 68, 0.1);
            font-weight: bold;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ config('app.name', 'EMS') }}</h1>
            <p>Expense Management System</p>
        </div>
        
        <!-- Report Title -->
        <div class="company-info">
            <h2>{{ $title ?? 'Expense Report' }}</h2>
            <p>Generated on: {{ now()->format('d M Y, H:i') }}</p>
        </div>
        
        <!-- Report Meta -->
        <div class="report-meta">
            <table>
                <tr>
                    <td class="label">Report Period:</td>
                    <td class="value">{{ $filters['start_date'] ?? 'All Time' }} - {{ $filters['end_date'] ?? 'Present' }}</td>
                </tr>
                @if(isset($filters['status']))
                <tr>
                    <td class="label">Status Filter:</td>
                    <td class="value">{{ ucfirst(str_replace('_', ' ', $filters['status'])) }}</td>
                </tr>
                @endif
                @if(isset($filters['category']))
                <tr>
                    <td class="label">Category:</td>
                    <td class="value">{{ $filters['category'] }}</td>
                </tr>
                @endif
                @if(isset($filters['employee']))
                <tr>
                    <td class="label">Employee:</td>
                    <td class="value">{{ $filters['employee'] }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Generated By:</td>
                    <td class="value">{{ $generatedBy ?? 'System' }}</td>
                </tr>
            </table>
        </div>
        
        <!-- Summary -->
        @if(isset($summary))
        <div class="summary-box">
            <div class="summary-item">
                <div class="number">{{ $summary['total_count'] ?? 0 }}</div>
                <div class="label">Total Expenses</div>
            </div>
            <div class="summary-item">
                <div class="number">Rp {{ number_format($summary['total_amount'] ?? 0, 0, ',', '.') }}</div>
                <div class="label">Total Amount</div>
            </div>
            <div class="summary-item">
                <div class="number">{{ $summary['approved_count'] ?? 0 }}</div>
                <div class="label">Approved</div>
            </div>
            <div class="summary-item">
                <div class="number">Rp {{ number_format($summary['paid_amount'] ?? 0, 0, ',', '.') }}</div>
                <div class="label">Paid Amount</div>
            </div>
        </div>
        @endif
        
        <!-- Expenses Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 12%">Date</th>
                    <th style="width: 15%">Employee</th>
                    <th style="width: 12%">Category</th>
                    <th style="width: 25%">Description</th>
                    <th style="width: 15%" class="text-right">Amount</th>
                    <th style="width: 10%" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $index => $expense)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                    <td>{{ $expense->user->name ?? 'N/A' }}</td>
                    <td>{{ $expense->category->name ?? 'N/A' }}</td>
                    <td>{{ Str::limit($expense->description, 50) }}</td>
                    <td class="text-right">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="status status-{{ $expense->status }}">
                            {{ str_replace('_', ' ', $expense->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No expenses found</td>
                </tr>
                @endforelse
                
                @if($expenses->count() > 0)
                <tr class="totals-row">
                    <td colspan="5" class="text-right">TOTAL:</td>
                    <td class="text-right">Rp {{ number_format($expenses->sum('amount'), 0, ',', '.') }}</td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>
        
        <!-- Signatures -->
        @if(isset($showSignatures) && $showSignatures)
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-label">Prepared By</div>
                    <div class="signature-name">{{ $generatedBy ?? '________________' }}</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-label">Reviewed By</div>
                    <div class="signature-name">________________</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-label">Approved By</div>
                    <div class="signature-name">________________</div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <p>This is a computer-generated document. No signature is required unless specified.</p>
            <p>{{ config('app.name', 'EMS') }} - Expense Management System &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>

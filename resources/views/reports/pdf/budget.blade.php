<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Budget Report</title>
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
            border-bottom: 2px solid #10b981;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 24px;
            color: #059669;
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
            width: 33.33%;
            padding: 15px;
            text-align: center;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        
        .summary-card.success {
            background-color: #d1fae5;
            border-color: #a7f3d0;
        }
        
        .summary-card.warning {
            background-color: #fef3c7;
            border-color: #fde68a;
        }
        
        .summary-card.danger {
            background-color: #fee2e2;
            border-color: #fecaca;
        }
        
        .summary-card .number {
            font-size: 18px;
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
            background-color: #059669;
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
        
        .progress-bar {
            width: 100%;
            height: 12px;
            background-color: #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .progress-bar .fill {
            height: 100%;
            border-radius: 6px;
        }
        
        .progress-bar .fill.green {
            background-color: #10b981;
        }
        
        .progress-bar .fill.yellow {
            background-color: #f59e0b;
        }
        
        .progress-bar .fill.red {
            background-color: #ef4444;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .status-badge.healthy {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-badge.warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-badge.critical {
            background-color: #fee2e2;
            color: #991b1b;
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
            <h2>Budget Utilization Report</h2>
            <p>Generated on: {{ now()->format('d M Y, H:i') }}</p>
            @if(isset($period))
            <p>Period: {{ $period }}</p>
            @endif
        </div>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="number">Rp {{ number_format($totalBudget ?? 0, 0, ',', '.') }}</div>
                <div class="label">Total Budget</div>
            </div>
            <div class="summary-card {{ ($usedPercentage ?? 0) > 80 ? 'danger' : (($usedPercentage ?? 0) > 60 ? 'warning' : 'success') }}">
                <div class="number">Rp {{ number_format($totalUsed ?? 0, 0, ',', '.') }}</div>
                <div class="label">Total Used ({{ number_format($usedPercentage ?? 0, 1) }}%)</div>
            </div>
            <div class="summary-card success">
                <div class="number">Rp {{ number_format($totalRemaining ?? 0, 0, ',', '.') }}</div>
                <div class="label">Remaining Budget</div>
            </div>
        </div>
        
        <!-- Budget Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 20%">Category</th>
                    <th style="width: 15%" class="text-right">Budget</th>
                    <th style="width: 15%" class="text-right">Used</th>
                    <th style="width: 15%" class="text-right">Remaining</th>
                    <th style="width: 20%">Progress</th>
                    <th style="width: 10%" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($budgets as $index => $budget)
                @php
                    $percentage = $budget->budget_amount > 0 ? ($budget->used_amount / $budget->budget_amount * 100) : 0;
                    $status = $percentage >= 90 ? 'critical' : ($percentage >= 70 ? 'warning' : 'healthy');
                    $progressColor = $percentage >= 90 ? 'red' : ($percentage >= 70 ? 'yellow' : 'green');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $budget->category->name ?? 'N/A' }}</td>
                    <td class="text-right">Rp {{ number_format($budget->budget_amount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($budget->used_amount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($budget->remaining_amount, 0, ',', '.') }}</td>
                    <td>
                        <div class="progress-bar">
                            <div class="fill {{ $progressColor }}" style="width: {{ min($percentage, 100) }}%"></div>
                        </div>
                        <div style="text-align: center; font-size: 9px; margin-top: 2px;">{{ number_format($percentage, 1) }}%</div>
                    </td>
                    <td class="text-center">
                        <span class="status-badge {{ $status }}">
                            {{ ucfirst($status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No budgets found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="footer">
            <p>Budget thresholds: Healthy (< 70%), Warning (70-89%), Critical (≥ 90%)</p>
            <p>{{ config('app.name', 'EMS') }} - Expense Management System &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>

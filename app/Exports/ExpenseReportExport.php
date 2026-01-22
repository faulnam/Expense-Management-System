<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected string $startDate;
    protected string $endDate;
    protected ?string $department;

    public function __construct(string $startDate, string $endDate, ?string $department = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->department = $department;
    }

    public function collection()
    {
        $query = Expense::with(['category', 'user'])
            ->whereBetween('expense_date', [$this->startDate, $this->endDate])
            ->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_PAID]);

        if ($this->department) {
            $query->whereHas('user', fn($q) => $q->where('department', $this->department));
        }

        return $query->orderBy('expense_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Expense Number',
            'Date',
            'Employee',
            'Department',
            'Category',
            'Description',
            'Amount',
            'Status',
            'Submitted At',
            'Approved At',
            'Paid At',
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->expense_number,
            $expense->expense_date->format('Y-m-d'),
            $expense->user->name,
            $expense->user->department,
            $expense->category->name,
            $expense->description,
            $expense->amount,
            ucfirst($expense->status),
            $expense->submitted_at?->format('Y-m-d H:i'),
            $expense->approved_at?->format('Y-m-d H:i'),
            $expense->paid_at?->format('Y-m-d H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

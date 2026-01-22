@component('mail::message')
# New Expense Pending Approval

Hello {{ $manager->name }},

A new expense claim from **{{ $expense->user->name }}** is waiting for your review.

## Expense Details

| | |
|:---|:---|
| **Reference** | {{ $expense->reference_number }} |
| **Employee** | {{ $expense->user->name }} |
| **Department** | {{ $expense->user->department ?? 'N/A' }} |
| **Date** | {{ $expense->expense_date->format('d M Y') }} |
| **Category** | {{ $expense->category->name }} |
| **Amount** | Rp {{ number_format($expense->amount, 0, ',', '.') }} |
| **Description** | {{ $expense->description }} |

@if($expense->receipt_path)
@component('mail::panel')
📎 Receipt attached to this expense
@endcomponent
@endif

@component('mail::button', ['url' => route('approvals.index'), 'color' => 'primary'])
Review Expense
@endcomponent

Please review this expense at your earliest convenience.

Thanks,<br>
{{ config('app.name') }}

---
<small>This is an automated message from the Expense Management System. Please do not reply to this email.</small>
@endcomponent

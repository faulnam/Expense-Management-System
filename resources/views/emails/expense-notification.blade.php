@component('mail::message')
# Expense {{ ucfirst($action) }}

Hello {{ $expense->user->name }},

@if($action === 'submitted')
Your expense claim has been successfully submitted and is pending approval.
@elseif($action === 'approved')
Great news! Your expense claim has been approved.
@elseif($action === 'rejected')
Unfortunately, your expense claim has been rejected.

**Reason:** {{ $reason ?? 'No reason provided' }}
@elseif($action === 'paid')
Your expense reimbursement has been processed and the payment is on its way!
@endif

## Expense Details

| | |
|:---|:---|
| **Reference** | {{ $expense->reference_number }} |
| **Date** | {{ $expense->expense_date->format('d M Y') }} |
| **Category** | {{ $expense->category->name }} |
| **Amount** | Rp {{ number_format($expense->amount, 0, ',', '.') }} |
| **Description** | {{ $expense->description }} |

@if($action === 'approved' || $action === 'paid')
@component('mail::panel')
**Status:** {{ ucfirst($expense->status) }}

@if($expense->isPaid() && $expense->paid_at)
**Payment Date:** {{ $expense->paid_at->format('d M Y') }}
@endif
@endcomponent
@endif

@component('mail::button', ['url' => route('expenses.show', $expense)])
View Expense
@endcomponent

If you have any questions, please contact your manager or the finance department.

Thanks,<br>
{{ config('app.name') }}

---
<small>This is an automated message from the Expense Management System. Please do not reply to this email.</small>
@endcomponent

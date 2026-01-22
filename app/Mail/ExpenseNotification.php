<?php

namespace App\Mail;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpenseNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Expense $expense;
    public string $action;
    public ?string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Expense $expense, string $action, ?string $reason = null)
    {
        $this->expense = $expense;
        $this->action = $action;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'submitted' => 'Expense Submitted - ' . $this->expense->reference_number,
            'approved' => 'Expense Approved - ' . $this->expense->reference_number,
            'rejected' => 'Expense Rejected - ' . $this->expense->reference_number,
            'paid' => 'Expense Paid - ' . $this->expense->reference_number,
        ];

        return new Envelope(
            subject: $subjects[$this->action] ?? 'Expense Update - ' . $this->expense->reference_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.expense-notification',
            with: [
                'expense' => $this->expense,
                'action' => $this->action,
                'reason' => $this->reason,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

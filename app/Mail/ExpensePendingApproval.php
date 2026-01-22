<?php

namespace App\Mail;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpensePendingApproval extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Expense $expense;
    public User $manager;

    /**
     * Create a new message instance.
     */
    public function __construct(Expense $expense, User $manager)
    {
        $this->expense = $expense;
        $this->manager = $manager;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Expense Pending Approval - ' . $this->expense->reference_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.expense-pending-approval',
            with: [
                'expense' => $this->expense,
                'manager' => $this->manager,
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

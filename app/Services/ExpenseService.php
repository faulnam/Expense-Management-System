<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Approval;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ExpenseService
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function create(array $data, User $user): Expense
    {
        return DB::transaction(function () use ($data, $user) {
            $expense = new Expense();
            $expense->user_id = $user->id;
            $expense->category_id = $data['category_id'];
            $expense->expense_date = $data['expense_date'];
            $expense->amount = $data['amount'];
            $expense->description = $data['description'];
            $expense->status = Expense::STATUS_DRAFT;

            if (isset($data['receipt']) && $data['receipt'] instanceof UploadedFile) {
                // Ensure the upload was successful
                if (!$data['receipt']->isValid()) {
                    throw new \Exception('The receipt file upload failed. Please try again.');
                }
                
                $path = $data['receipt']->store('receipts/' . $user->id, 'public');
                
                if (!$path) {
                    throw new \Exception('Failed to save receipt file. Please check storage permissions.');
                }
                
                $expense->receipt_path = $path;
                $expense->receipt_original_name = $data['receipt']->getClientOriginalName();
            }

            $expense->save();

            $this->auditService->logCreate(
                Expense::class,
                $expense->id,
                "Expense {$expense->expense_number} created by {$user->name}",
                $expense->toArray()
            );

            return $expense;
        });
    }

    public function update(Expense $expense, array $data): Expense
    {
        return DB::transaction(function () use ($expense, $data) {
            $oldValues = $expense->toArray();

            $expense->category_id = $data['category_id'] ?? $expense->category_id;
            $expense->expense_date = $data['expense_date'] ?? $expense->expense_date;
            $expense->amount = $data['amount'] ?? $expense->amount;
            $expense->description = $data['description'] ?? $expense->description;

            if (isset($data['receipt']) && $data['receipt'] instanceof UploadedFile) {
                // Delete old receipt
                if ($expense->receipt_path) {
                    Storage::disk('public')->delete($expense->receipt_path);
                }
                
                $path = $data['receipt']->store('receipts/' . $expense->user_id, 'public');
                $expense->receipt_path = $path;
                $expense->receipt_original_name = $data['receipt']->getClientOriginalName();
            }

            $expense->save();

            $this->auditService->logUpdate(
                Expense::class,
                $expense->id,
                "Expense {$expense->expense_number} updated",
                $oldValues,
                $expense->toArray()
            );

            return $expense;
        });
    }

    public function submit(Expense $expense): Expense
    {
        return DB::transaction(function () use ($expense) {
            if (!$expense->canBeSubmitted()) {
                throw new \Exception('Expense cannot be submitted in its current state.');
            }

            $expense->status = Expense::STATUS_SUBMITTED;
            $expense->submitted_at = now();
            $expense->rejection_reason = null;
            $expense->save();

            // Check budget warning
            $this->checkBudgetWarning($expense);

            // Create approval record based on who submitted
            $submitter = $expense->user;
            
            if ($submitter->isAdmin()) {
                // Admin expense: auto-approved (or can self-approve)
                $expense->status = Expense::STATUS_APPROVED;
                $expense->approved_at = now();
                $expense->save();
                
                Approval::create([
                    'expense_id' => $expense->id,
                    'approver_id' => $submitter->id,
                    'stage' => Approval::STAGE_ADMIN,
                    'status' => Approval::STATUS_APPROVED,
                    'notes' => 'Auto-approved (Admin expense)',
                    'actioned_at' => now(),
                ]);
            } elseif ($submitter->isFinance()) {
                // Finance expense: needs Admin approval
                $admin = User::whereHas('role', fn($q) => $q->where('slug', 'admin'))->first();
                if ($admin) {
                    Approval::create([
                        'expense_id' => $expense->id,
                        'approver_id' => $admin->id,
                        'stage' => Approval::STAGE_ADMIN,
                        'status' => Approval::STATUS_PENDING,
                    ]);
                }
            } elseif ($submitter->isManager()) {
                // Manager expense: needs Finance approval only
                $finance = User::whereHas('role', fn($q) => $q->where('slug', 'finance'))->first();
                if ($finance) {
                    Approval::create([
                        'expense_id' => $expense->id,
                        'approver_id' => $finance->id,
                        'stage' => Approval::STAGE_FINANCE,
                        'status' => Approval::STATUS_PENDING,
                    ]);
                }
            } else {
                // Employee expense: needs Manager approval first
                if ($submitter->manager_id) {
                    Approval::create([
                        'expense_id' => $expense->id,
                        'approver_id' => $submitter->manager_id,
                        'stage' => Approval::STAGE_MANAGER,
                        'status' => Approval::STATUS_PENDING,
                    ]);
                }
            }

            $this->auditService->logSubmit(
                Expense::class,
                $expense->id,
                "Expense {$expense->expense_number} submitted for approval"
            );

            return $expense;
        });
    }

    public function approve(Expense $expense, User $approver, ?string $notes = null): Expense
    {
        return DB::transaction(function () use ($expense, $approver, $notes) {
            // Find pending approval for this approver OR any pending approval if admin
            $approval = $expense->approvals()
                ->where('status', Approval::STATUS_PENDING)
                ->where(function($q) use ($approver) {
                    $q->where('approver_id', $approver->id);
                    if ($approver->isAdmin()) {
                        $q->orWhereNotNull('id'); // Admin can approve any pending
                    }
                })
                ->first();

            if (!$approval) {
                throw new \Exception('No pending approval found for this user.');
            }

            // Mark current approval as approved
            $approval->status = Approval::STATUS_APPROVED;
            $approval->approver_id = $approver->id; // Update if admin took over
            $approval->notes = $notes;
            $approval->actioned_at = now();
            $approval->save();

            // Determine next step based on current stage and expense owner
            $expenseOwner = $expense->user;
            
            if ($approval->stage === Approval::STAGE_MANAGER) {
                // Manager approved, now needs Finance approval
                $expense->status = Expense::STATUS_MANAGER_APPROVED;
                $expense->save();
                
                // Create Finance approval record
                $finance = User::whereHas('role', fn($q) => $q->where('slug', 'finance'))->first();
                if ($finance) {
                    Approval::create([
                        'expense_id' => $expense->id,
                        'approver_id' => $finance->id,
                        'stage' => Approval::STAGE_FINANCE,
                        'status' => Approval::STATUS_PENDING,
                    ]);
                }
            } elseif ($approval->stage === Approval::STAGE_FINANCE) {
                // Finance approved, expense is fully approved
                $expense->status = Expense::STATUS_APPROVED;
                $expense->approved_at = now();
                $expense->save();
                
                // Update budget used amount
                $this->updateBudgetUsage($expense);
            } elseif ($approval->stage === Approval::STAGE_ADMIN) {
                // Admin approved (for Finance staff expenses)
                $expense->status = Expense::STATUS_APPROVED;
                $expense->approved_at = now();
                $expense->save();
                
                // Update budget used amount
                $this->updateBudgetUsage($expense);
            }

            $this->auditService->logApprove(
                Expense::class,
                $expense->id,
                "Expense {$expense->expense_number} approved by {$approver->name} at stage {$approval->stage}",
                $notes
            );

            return $expense;
        });
    }

    public function reject(Expense $expense, User $approver, string $reason): Expense
    {
        return DB::transaction(function () use ($expense, $approver, $reason) {
            $approval = $expense->approvals()
                ->where('approver_id', $approver->id)
                ->where('status', Approval::STATUS_PENDING)
                ->first();

            if (!$approval) {
                throw new \Exception('No pending approval found for this user.');
            }

            $approval->status = Approval::STATUS_REJECTED;
            $approval->notes = $reason;
            $approval->actioned_at = now();
            $approval->save();

            $expense->status = Expense::STATUS_REJECTED;
            $expense->rejection_reason = $reason;
            $expense->rejected_at = now();
            $expense->save();

            $this->auditService->logReject(
                Expense::class,
                $expense->id,
                "Expense {$expense->expense_number} rejected by {$approver->name}",
                $reason
            );

            return $expense;
        });
    }

    public function flag(Expense $expense, User $flagger, string $reason): Expense
    {
        $expense->is_flagged = true;
        $expense->flag_reason = $reason;
        $expense->flagged_by = $flagger->id;
        $expense->flagged_at = now();
        $expense->save();

        $this->auditService->logFlag(
            Expense::class,
            $expense->id,
            "Expense {$expense->expense_number} flagged by {$flagger->name}",
            $reason
        );

        return $expense;
    }

    public function unflag(Expense $expense): Expense
    {
        $expense->is_flagged = false;
        $expense->flag_reason = null;
        $expense->flagged_by = null;
        $expense->flagged_at = null;
        $expense->save();

        return $expense;
    }

    public function delete(Expense $expense): bool
    {
        $this->auditService->logDelete(
            Expense::class,
            $expense->id,
            "Expense {$expense->expense_number} deleted",
            $expense->toArray()
        );

        return $expense->delete();
    }

    protected function checkBudgetWarning(Expense $expense): ?array
    {
        $budget = Budget::findForExpense($expense);
        
        if (!$budget) {
            return null;
        }

        $projectedUsage = $budget->used_amount + $expense->amount;
        $projectedPercentage = ($projectedUsage / $budget->limit_amount) * 100;

        if ($projectedPercentage > 100) {
            return [
                'type' => 'over_budget',
                'message' => 'This expense will exceed the budget limit.',
                'current_usage' => $budget->used_amount,
                'limit' => $budget->limit_amount,
                'projected' => $projectedUsage,
            ];
        }

        if ($projectedPercentage >= $budget->warning_threshold) {
            return [
                'type' => 'warning',
                'message' => 'This expense will reach the budget warning threshold.',
                'current_usage' => $budget->used_amount,
                'limit' => $budget->limit_amount,
                'projected' => $projectedUsage,
            ];
        }

        return null;
    }

    protected function updateBudgetUsage(Expense $expense): void
    {
        $budget = Budget::findForExpense($expense);
        
        if ($budget) {
            $budget->used_amount += $expense->amount;
            $budget->save();
        }
    }

    public function checkForFraud(Expense $expense): ?array
    {
        $warnings = [];

        // Check if amount is unusually high for the category
        $avgAmount = Expense::where('category_id', $expense->category_id)
            ->where('status', Expense::STATUS_PAID)
            ->avg('amount');

        if ($avgAmount && $expense->amount > ($avgAmount * 3)) {
            $warnings[] = [
                'type' => 'high_amount',
                'message' => 'Amount is significantly higher than average for this category.',
                'average' => $avgAmount,
                'submitted' => $expense->amount,
            ];
        }

        // Check for duplicate submissions
        $duplicates = Expense::where('user_id', $expense->user_id)
            ->where('category_id', $expense->category_id)
            ->where('amount', $expense->amount)
            ->where('expense_date', $expense->expense_date)
            ->where('id', '!=', $expense->id)
            ->count();

        if ($duplicates > 0) {
            $warnings[] = [
                'type' => 'potential_duplicate',
                'message' => 'This expense may be a duplicate submission.',
                'similar_count' => $duplicates,
            ];
        }

        // Check submission frequency
        $recentSubmissions = Expense::where('user_id', $expense->user_id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($recentSubmissions > 10) {
            $warnings[] = [
                'type' => 'high_frequency',
                'message' => 'User has submitted many expenses recently.',
                'count' => $recentSubmissions,
            ];
        }

        return empty($warnings) ? null : $warnings;
    }
}

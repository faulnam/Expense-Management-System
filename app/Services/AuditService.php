<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditService
{
    public function log(
        string $action,
        string $description,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return AuditLog::log($action, $description, $modelType, $modelId, $oldValues, $newValues);
    }

    public function logLogin(User $user): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_LOGIN,
            "User {$user->name} logged in",
            User::class,
            $user->id
        );
    }

    public function logLogout(User $user): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_LOGOUT,
            "User {$user->name} logged out",
            User::class,
            $user->id
        );
    }

    public function logCreate(string $modelType, int $modelId, string $description, array $newValues = []): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_CREATE,
            $description,
            $modelType,
            $modelId,
            null,
            $newValues
        );
    }

    public function logUpdate(string $modelType, int $modelId, string $description, array $oldValues = [], array $newValues = []): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_UPDATE,
            $description,
            $modelType,
            $modelId,
            $oldValues,
            $newValues
        );
    }

    public function logDelete(string $modelType, int $modelId, string $description, array $oldValues = []): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_DELETE,
            $description,
            $modelType,
            $modelId,
            $oldValues,
            null
        );
    }

    public function logSubmit(string $modelType, int $modelId, string $description): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_SUBMIT,
            $description,
            $modelType,
            $modelId
        );
    }

    public function logApprove(string $modelType, int $modelId, string $description, ?string $notes = null): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_APPROVE,
            $description,
            $modelType,
            $modelId,
            null,
            $notes ? ['notes' => $notes] : null
        );
    }

    public function logReject(string $modelType, int $modelId, string $description, ?string $reason = null): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_REJECT,
            $description,
            $modelType,
            $modelId,
            null,
            $reason ? ['reason' => $reason] : null
        );
    }

    public function logPayment(string $modelType, int $modelId, string $description, array $paymentDetails = []): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_PAYMENT,
            $description,
            $modelType,
            $modelId,
            null,
            $paymentDetails
        );
    }

    public function logFlag(string $modelType, int $modelId, string $description, ?string $reason = null): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_FLAG,
            $description,
            $modelType,
            $modelId,
            null,
            $reason ? ['reason' => $reason] : null
        );
    }
}

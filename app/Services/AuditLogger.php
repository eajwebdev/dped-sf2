<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Record an activity entry. Safe to call anywhere; never throws on logging failure.
     */
    public function log(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): void {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'auditable_type' => $subject ? $subject->getMorphClass() : null,
                'auditable_id' => $subject?->getKey(),
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip(),
                'user_agent' => substr((string) Request::userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Convenience helpers for the common CRUD verbs. */
    public function created(Model $subject, ?string $description = null): void
    {
        $this->log('created', $subject, $description ?? $this->label($subject).' created', null, $subject->getAttributes());
    }

    public function updated(Model $subject, array $original, ?string $description = null): void
    {
        $this->log('updated', $subject, $description ?? $this->label($subject).' updated',
            array_intersect_key($original, $subject->getChanges()), $subject->getChanges());
    }

    public function deleted(Model $subject, ?string $description = null): void
    {
        $this->log('deleted', $subject, $description ?? $this->label($subject).' deleted', $subject->getAttributes(), null);
    }

    protected function label(Model $subject): string
    {
        return class_basename($subject);
    }
}

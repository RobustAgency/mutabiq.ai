<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use App\Enums\ActivityLog\ActivityAction;

abstract class ActivityAwareObserver
{
    /**
     * Get the tracked fields for change capture.
     * Override this in child observers to specify which fields to track.
     *
     * @return array<int, string>
     */
    protected function getTrackedFields(): array
    {
        return [];
    }

    /**
     * Get common metadata for all activity logs.
     *
     * @return array<string, string|null>
     */
    protected function getClientMetadata(): array
    {
        return [
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ];
    }

    /**
     * Get organization_id for the model. Override in child observers if model doesn't have organization_id.
     */
    protected function getModelOrganizationId(Model $model): ?int
    {
        return $model->organization_id ?? Auth::user()?->organization_id;
    }

    /**
     * Log activity for model creation.
     */
    protected function logCreate(Model $model): void
    {
        $organizationId = $this->getModelOrganizationId($model);

        // Only create activity log if we have an organization_id
        if ($organizationId === null) {
            return;
        }

        $metadata = $this->getClientMetadata();
        ActivityLog::create([
            'organization_id' => $organizationId,
            'user_id' => Auth::id(),
            'actable_type' => $model::class,
            'actable_id' => $model->getKey(),
            'action' => ActivityAction::CREATE->value,
            'description' => class_basename($model).' created',
            'changes' => [],
            'ip_address' => $metadata['ip_address'],
            'user_agent' => $metadata['user_agent'],
        ]);
    }

    /**
     * Log activity for model update with change tracking.
     */
    protected function logUpdate(Model $model, array $originalData): void
    {
        $trackedFields = $this->getTrackedFields();
        $changes = [];

        if (! empty($trackedFields)) {
            foreach ($trackedFields as $field) {
                $original = $originalData[$field] ?? null;
                $updated = $model->{$field};

                if ($original !== $updated) {
                    $changes[$field] = [
                        'from' => $original,
                        'to' => $updated,
                    ];
                }
            }
        }

        // Only log if there are actual changes
        if (empty($changes) && ! empty($trackedFields)) {
            return;
        }

        $organizationId = $this->getModelOrganizationId($model);

        // Only create activity log if we have an organization_id
        if ($organizationId === null) {
            return;
        }

        $metadata = $this->getClientMetadata();
        ActivityLog::create([
            'organization_id' => $organizationId,
            'user_id' => Auth::id(),
            'actable_type' => $model::class,
            'actable_id' => $model->getKey(),
            'action' => ActivityAction::UPDATE->value,
            'description' => class_basename($model).' updated',
            'changes' => $changes,
            'ip_address' => $metadata['ip_address'],
            'user_agent' => $metadata['user_agent'],
        ]);
    }

    /**
     * Log activity for model deletion.
     */
    protected function logDelete(Model $model): void
    {
        $organizationId = $this->getModelOrganizationId($model);

        // Only create activity log if we have an organization_id
        if ($organizationId === null) {
            return;
        }

        $metadata = $this->getClientMetadata();
        ActivityLog::create([
            'organization_id' => $organizationId,
            'user_id' => Auth::id(),
            'actable_type' => $model::class,
            'actable_id' => $model->getKey(),
            'action' => ActivityAction::DELETE->value,
            'description' => class_basename($model).' deleted',
            'changes' => [],
            'ip_address' => $metadata['ip_address'],
            'user_agent' => $metadata['user_agent'],
        ]);
    }
}

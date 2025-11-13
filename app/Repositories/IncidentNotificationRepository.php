<?php

namespace App\Repositories;

use App\Models\IncidentNotification;
use Illuminate\Pagination\LengthAwarePaginator;

class IncidentNotificationRepository
{
    /**
     * @return LengthAwarePaginator<int, IncidentNotification>
     */
    public function getFilteredIncidentNotifications(array $filters = []): LengthAwarePaginator
    {
        $query = IncidentNotification::with(['aiIncident']);

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['audience_type'])) {
            $query->where('audience_type', $filters['audience_type']);
        }

        if (isset($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }

        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a new incident notification.
     */
    public function createIncidentNotification(array $data): IncidentNotification
    {
        return IncidentNotification::create($data);
    }

    /**
     * Update an existing incident notification.
     */
    public function updateIncidentNotification(IncidentNotification $incidentNotification, array $data): IncidentNotification
    {
        $incidentNotification->update($data);
        return $incidentNotification->fresh();
    }

    /**
     * Delete an incident notification.
     */
    public function deleteIncidentNotification(IncidentNotification $incidentNotification): bool
    {
        return $incidentNotification->delete();
    }

    /**
     * Get an incident notification by ID.
     */
    public function getIncidentNotificationById(IncidentNotification $incidentNotification): ?IncidentNotification
    {
        return $incidentNotification->load('aiIncident');
    }
}

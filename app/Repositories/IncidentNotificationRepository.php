<?php

namespace App\Repositories;

use App\Models\IncidentNotification;
use Illuminate\Pagination\LengthAwarePaginator;

class IncidentNotificationRepository
{
    /**
     * @return LengthAwarePaginator<int, IncidentNotification>
     */
    public function getPaginatedIncidentNotifications(int $perPage = 15): LengthAwarePaginator
    {
        return IncidentNotification::with('aiIncident')
            ->paginate($perPage);
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

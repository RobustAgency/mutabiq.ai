<?php

namespace App\Repositories;

use App\Models\IncidentAlert;
use Illuminate\Pagination\LengthAwarePaginator;

class IncidentAlertRepository
{
    /**
     * @return LengthAwarePaginator<int, IncidentAlert>
     */
    public function getPaginatedIncidentAlerts(int $organizationID, int $perPage = 15): LengthAwarePaginator
    {
        return IncidentAlert::with('aiIncident')
            ->where('organization_id', $organizationID)
            ->paginate($perPage);
    }

    /**
     * Create a new incident alert.
     */
    public function createIncidentAlert(array $data): IncidentAlert
    {
        return IncidentAlert::create($data);
    }

    /**
     * Update an existing incident alert.
     */
    public function updateIncidentAlert(IncidentAlert $incidentAlert, array $data): IncidentAlert
    {
        $incidentAlert->update($data);
        return $incidentAlert->fresh();
    }

    /**
     * Delete an incident alert.
     */
    public function deleteIncidentAlert(IncidentAlert $incidentAlert): bool
    {
        return $incidentAlert->delete();
    }

    /**
     * Get an incident alert by ID.
     */
    public function getIncidentAlertById(IncidentAlert $incidentAlert): ?IncidentAlert
    {
        return $incidentAlert->load('aiIncident');
    }
}

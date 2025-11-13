<?php

namespace App\Repositories;

use App\Models\IncidentAlert;
use Illuminate\Pagination\LengthAwarePaginator;

class IncidentAlertRepository
{
    /**
     * @return LengthAwarePaginator<int, IncidentAlert>
     */
    public function getFilteredIncidentAlerts(array $filters = []): LengthAwarePaginator
    {
        $query = IncidentAlert::with('aiIncident');

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['source_type'])) {
            $query->where('source_type', $filters['source_type']);
        }

        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
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

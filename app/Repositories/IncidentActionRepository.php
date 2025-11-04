<?php

namespace App\Repositories;

use App\Models\IncidentAction;
use Illuminate\Pagination\LengthAwarePaginator;

class IncidentActionRepository
{
    /**
     * @return LengthAwarePaginator<int, IncidentAction>
     */
    public function getPaginatedIncidentActions(int $organizationID, int $perPage = 15): LengthAwarePaginator
    {
        return IncidentAction::with('aiIncident')
            ->where('organization_id', $organizationID)
            ->paginate($perPage);
    }

    /**
     * Create a new incident action.
     */
    public function createIncidentAction(array $data): IncidentAction
    {
        return IncidentAction::create($data);
    }

    /**
     * Update an existing incident action.
     */
    public function updateIncidentAction(IncidentAction $incidentAction, array $data): IncidentAction
    {
        $incidentAction->update($data);
        return $incidentAction->fresh();
    }

    /**
     * Delete an incident action.
     */
    public function deleteIncidentAction(IncidentAction $incidentAction): bool
    {
        return $incidentAction->delete();
    }

    /**
     * Get an incident action by ID.
     */
    public function getIncidentActionById(IncidentAction $incidentAction): ?IncidentAction
    {
        return $incidentAction->load('aiIncident');
    }
}

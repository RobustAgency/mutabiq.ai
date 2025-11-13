<?php

namespace App\Repositories;

use App\Models\IncidentAction;
use Illuminate\Pagination\LengthAwarePaginator;

class IncidentActionRepository
{
    /**
     * @return LengthAwarePaginator<int, IncidentAction>
     */
    public function getFilteredIncidentActions(array $filters = []): LengthAwarePaginator
    {
        $query = IncidentAction::query()->with('aiIncident');

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['action_type'])) {
            $query->where('action_type', $filters['action_type']);
        }

        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
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

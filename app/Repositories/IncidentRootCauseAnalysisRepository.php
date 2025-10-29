<?php

namespace App\Repositories;

use App\Models\IncidentRootCauseAnalysis;
use Illuminate\Pagination\LengthAwarePaginator;

class IncidentRootCauseAnalysisRepository
{
    /**
     * @return LengthAwarePaginator<int, IncidentRootCauseAnalysis>
     */
    public function getPaginatedIncidentRootCauseAnalyses(int $perPage = 15): LengthAwarePaginator
    {
        return IncidentRootCauseAnalysis::with('aiIncident')
            ->paginate($perPage);
    }

    /**
     * Create a new incident root cause analysis.
     */
    public function createIncidentRootCauseAnalysis(array $data): IncidentRootCauseAnalysis
    {
        return IncidentRootCauseAnalysis::create($data);
    }

    /**
     * Update an existing incident root cause analysis.
     */
    public function updateIncidentRootCauseAnalysis(IncidentRootCauseAnalysis $incidentRootCauseAnalysis, array $data): IncidentRootCauseAnalysis
    {
        $incidentRootCauseAnalysis->update($data);
        return $incidentRootCauseAnalysis->fresh();
    }

    /**
     * Delete an incident root cause analysis.
     */
    public function deleteIncidentRootCauseAnalysis(IncidentRootCauseAnalysis $incidentRootCauseAnalysis): bool
    {
        return $incidentRootCauseAnalysis->delete();
    }

    /**
     * Get an incident root cause analysis by ID.
     */
    public function getIncidentRootCauseAnalysisById(IncidentRootCauseAnalysis $incidentRootCauseAnalysis): ?IncidentRootCauseAnalysis
    {
        return $incidentRootCauseAnalysis->load('aiIncident');
    }
}

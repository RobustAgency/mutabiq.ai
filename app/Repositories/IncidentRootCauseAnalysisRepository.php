<?php

namespace App\Repositories;

use App\Models\IncidentRootCauseAnalysis;
use Illuminate\Pagination\LengthAwarePaginator;

class IncidentRootCauseAnalysisRepository
{
    /**
     * @return LengthAwarePaginator<int, IncidentRootCauseAnalysis>
     */
    public function getFilteredIncidentRootCauseAnalyses(array $filters = []): LengthAwarePaginator
    {
        $query = IncidentRootCauseAnalysis::query()->with(['aiIncident']);

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['rca_method'])) {
            $query->where('rca_method', $filters['rca_method']);
        }

        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
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

<?php

namespace App\Repositories;

use App\Models\ComplianceEvidence;
use Illuminate\Pagination\LengthAwarePaginator;

class ComplianceEvidenceRepository
{
    /**
     * Get paginated list of compliance evidence with specified filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, ComplianceEvidence>
     */
    public function getFilteredComplianceEvidence(array $filters = []): LengthAwarePaginator
    {
        $query = ComplianceEvidence::query();

        $query->when(! empty($filters['artifact_type']), function ($query) use ($filters) {
            $query->where('artifact_type', $filters['artifact_type']);
        });

        $query->when(! empty($filters['review_outcome']), function ($query) use ($filters) {
            $query->where('review_outcome', $filters['review_outcome']);
        });

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new compliance evidence record.
     */
    public function createComplianceEvidence(array $data): ComplianceEvidence
    {
        return ComplianceEvidence::create($data);
    }

    /**
     * Update an existing compliance evidence record.
     */
    public function updateComplianceEvidence(ComplianceEvidence $evidence, array $data): ComplianceEvidence
    {
        $evidence->update($data);

        return $evidence;
    }

    /**
     * Delete a compliance evidence record.
     */
    public function deleteComplianceEvidence(ComplianceEvidence $evidence): bool
    {
        return $evidence->delete();
    }
}

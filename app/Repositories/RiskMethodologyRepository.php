<?php

namespace App\Repositories;

use App\Models\RiskMethodology;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RiskMethodologyRepository
{
    /**
     * Get paginated risk methodologies.
     *
     * @return LengthAwarePaginator<int, RiskMethodology>
     */
    public function getPaginatedRiskMethodologies(int $organizationID, int $perPage = 15): LengthAwarePaginator
    {
        return RiskMethodology::query()
            ->where('organization_id', $organizationID)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new risk methodology.
     *
     * @param array<string, mixed> $data
     */
    public function createRiskMethodology(array $data): RiskMethodology
    {
        return RiskMethodology::create($data);
    }

    /**
     * Update an existing risk methodology.
     *
     * @param array<string, mixed> $data
     */
    public function updateRiskMethodology(RiskMethodology $riskMethodology, array $data): RiskMethodology
    {
        $riskMethodology->update($data);
        return $riskMethodology->fresh();
    }

    /**
     * Delete a risk methodology.
     */
    public function deleteRiskMethodology(RiskMethodology $riskMethodology): bool
    {
        return $riskMethodology->delete();
    }

    /**
     * Get a risk methodology by ID.
     */
    public function getRiskMethodologyByID(RiskMethodology $riskMethodology): ?RiskMethodology
    {
        return $riskMethodology->load('organization');
    }
}

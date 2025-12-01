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
    public function getFilteredRiskMethodologies(array $filters = []): LengthAwarePaginator
    {
        $query = RiskMethodology::query();

        $query->where('organization_id', $filters['organization_id']);

        if (! empty($filters['effective_from'])) {
            $query->where('effective_from', '>=', $filters['effective_from']);
        }

        if (! empty($filters['effective_to'])) {
            $query->where('effective_to', '<=', $filters['effective_to']);
        }

        if (! empty($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a new risk methodology.
     *
     * @param  array<string, mixed>  $data
     */
    public function createRiskMethodology(array $data): RiskMethodology
    {
        return RiskMethodology::create($data);
    }

    /**
     * Update an existing risk methodology.
     *
     * @param  array<string, mixed>  $data
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
     * Get a risk methodology.
     */
    public function getRiskMethodology(RiskMethodology $riskMethodology): ?RiskMethodology
    {
        return $riskMethodology->load('organization');
    }
}

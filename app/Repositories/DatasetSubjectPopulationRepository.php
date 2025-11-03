<?php

namespace App\Repositories;

use App\Models\DatasetSubjectPopulation;
use Illuminate\Pagination\LengthAwarePaginator;

class DatasetSubjectPopulationRepository
{
    /**
     * Get paginated dataset subject populations for a specific organization.
     * 
     * @param int $organizationId
     * @param int $perPage
     * @return LengthAwarePaginator<int, DatasetSubjectPopulation>
     */
    public function getPaginatedPopulations(int $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return DatasetSubjectPopulation::where('organization_id', $organizationId)
            ->with(['dataset', 'snapshot'])
            ->orderBy('as_of', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new dataset subject population record.
     */
    public function createPopulation(array $data): DatasetSubjectPopulation
    {
        if (!isset($data['created_at'])) {
            $data['created_at'] = now();
        }

        return DatasetSubjectPopulation::create($data);
    }

    /**
     * Update an existing dataset subject population record.
     */
    public function updatePopulation(DatasetSubjectPopulation $population, array $data): DatasetSubjectPopulation
    {
        $population->update($data);
        return $population->fresh();
    }

    /**
     * Delete a dataset subject population record.
     */
    public function deletePopulation(DatasetSubjectPopulation $population): bool
    {
        return $population->delete();
    }
}

<?php

namespace App\Repositories;

use App\Models\DatasetSubjectPopulation;
use Illuminate\Pagination\LengthAwarePaginator;

class DatasetSubjectPopulationRepository
{
    /**
     * Get paginated dataset subject populations for a specific organization.
     * 
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, DatasetSubjectPopulation>
     */
    public function getFilteredDatasetSubjectPopulations(array $filters = []): LengthAwarePaginator
    {
        $query = DatasetSubjectPopulation::query();

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['subject_realm'])) {
            $query->where('subject_realm', $filters['subject_realm']);
        }

        if (isset($filters['jurisdiction'])) {
            $query->where('jurisdiction', $filters['jurisdiction']);
        }

        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->with(['dataset', 'snapshot'])
            ->orderBy('as_of', 'desc')
            ->paginate($filters['per_page'] ?? 15);
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

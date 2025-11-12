<?php

namespace App\Repositories;

use App\Models\Dataset;
use Illuminate\Pagination\LengthAwarePaginator;

class DatasetRepository
{
    /**
     * Get filtered datasets for a specific organization.
     *
     * @param array $filters
     * @return LengthAwarePaginator<int, Dataset>
     */
    public function getFilteredDatasets(array $filters = []): LengthAwarePaginator
    {
        $query = Dataset::query()->with(['dataElements']);

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['sensitivity'])) {
            $query->where('sensitivity', $filters['sensitivity']);
        }

        if (isset($filters['contains_pii'])) {
            $query->where('contains_pii', $filters['contains_pii']);
        }

        if (isset($filters['controller_role'])) {
            $query->where('controller_role', $filters['controller_role']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Get dataset by ID.
     *
     * @param int $id
     * @return Dataset|null
     */
    public function getDatasetByID(int $id): ?Dataset
    {
        return Dataset::find($id);
    }

    /**
     * Create a new dataset.
     *
     * @param array $data
     * @return Dataset
     */
    public function createDataset(array $data): Dataset
    {
        return Dataset::create($data);
    }

    /**
     * Update a dataset.
     *
     * @param Dataset $dataset
     * @param array $data
     * @return bool
     */
    public function updateDataset(Dataset $dataset, array $data): bool
    {
        return $dataset->update($data);
    }

    /**
     * Delete a dataset.
     *
     * @param Dataset $dataset
     * @return bool
     */
    public function delete(Dataset $dataset): bool
    {
        return $dataset->delete();
    }
}

<?php

namespace App\Repositories;

use App\Models\Dataset;
use Illuminate\Pagination\LengthAwarePaginator;

class DatasetRepository
{
    /**
     * Get filtered datasets for a specific organization.
     *
     * @return LengthAwarePaginator<int, Dataset>
     */
    public function getFilteredDatasets(array $filters = []): LengthAwarePaginator
    {
        $query = Dataset::query()->with(['dataElements']);

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        if (isset($filters['sensitivity'])) {
            $query->where('sensitivity', $filters['sensitivity']);
        }

        if (isset($filters['contains_personal_data'])) {
            $query->where('contains_personal_data', $filters['contains_personal_data']);
        }

        if (isset($filters['data_steward'])) {
            $query->where('data_steward', 'like', '%'.$filters['data_steward'].'%');
        }

        if (isset($filters['license_type'])) {
            $query->where('license_type', $filters['license_type']);
        }

        if (isset($filters['purpose'])) {
            $query->where('purpose', $filters['purpose']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Get dataset by ID.
     */
    public function getDatasetByID(int $id): ?Dataset
    {
        return Dataset::find($id);
    }

    /**
     * Create a new dataset.
     */
    public function createDataset(array $data): Dataset
    {
        return Dataset::create($data);
    }

    /**
     * Update a dataset.
     */
    public function updateDataset(Dataset $dataset, array $data): bool
    {
        return $dataset->update($data);
    }

    /**
     * Delete a dataset.
     */
    public function delete(Dataset $dataset): bool
    {
        return $dataset->delete();
    }
}

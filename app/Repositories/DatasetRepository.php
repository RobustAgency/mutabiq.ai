<?php

namespace App\Repositories;

use App\Models\Dataset;
use Illuminate\Pagination\LengthAwarePaginator;

class DatasetRepository
{
    /**
     * Get paginated datasets.
     *
     * @param int $perPage
     * @return LengthAwarePaginator<int, Dataset>
     */
    public function getPaginatedDatasets(int $perPage = 15): LengthAwarePaginator
    {
        $query = Dataset::with(['dataElements']);
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

<?php

namespace App\Repositories;

use App\Models\AiModelDataset;
use Illuminate\Pagination\LengthAwarePaginator;

class AiModelDatasetRepository
{
    /**
     * Get paginated AI model datasets for a specific organization.
     *
     * @param array $filters
     * @return LengthAwarePaginator<int, AiModelDataset>
     */
    public function getFilteredAiModelDatasets(array $filters = []): LengthAwarePaginator
    {
        $query = AiModelDataset::query();

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a new AI model dataset link.
     *
     * @param array $data
     * @return AiModelDataset
     */
    public function create(array $data): AiModelDataset
    {
        return AiModelDataset::create($data);
    }

    /**
     * Update an AI model dataset link.
     *
     * @param AiModelDataset $aiModelDataset
     * @param array $data
     * @return bool
     */
    public function update(AiModelDataset $aiModelDataset, array $data): bool
    {
        return $aiModelDataset->update($data);
    }
}

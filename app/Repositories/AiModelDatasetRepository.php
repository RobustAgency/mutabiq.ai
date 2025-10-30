<?php

namespace App\Repositories;

use App\Models\AiModel;
use App\Models\AiModelDataset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AiModelDatasetRepository
{
    /**
     * Get paginated AI model datasets.
     *
     * @param int $perPage
     * @return LengthAwarePaginator<int, AiModelDataset>
     */
    public function getPaginatedAiModelDatasets(int $perPage): LengthAwarePaginator
    {
        return AiModelDataset::paginate($perPage);
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

<?php

namespace App\Repositories;

use App\Models\AiAsset;
use Illuminate\Pagination\LengthAwarePaginator;

class AiAssetRepository
{
    /**
     * @return LengthAwarePaginator<int, AiAsset>
     */
    public function getPaginatedAiAssets(int $perPage = 15): LengthAwarePaginator
    {
        return AiAsset::with(['vendor', 'vendorAgreement'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new AI asset.
     */
    public function createAiAsset(array $data): AiAsset
    {
        return AiAsset::create($data);
    }

    /**
     * Update an existing AI asset.
     */
    public function updateAiAsset(AiAsset $aiAsset, array $data): AiAsset
    {
        $aiAsset->update($data);
        return $aiAsset->fresh();
    }

    /**
     * Delete an AI asset.
     */
    public function deleteAiAsset(AiAsset $aiAsset): bool
    {
        return $aiAsset->delete();
    }
}

<?php

namespace App\Repositories;

use App\Models\AiModel;
use App\Models\AiModelDataset;
use Illuminate\Database\Eloquent\Collection;

class AiModelRepository
{

    /**
     * Get all AI models by organization ID.
     *
     * @param int $organizationID
     * @return Collection<int, AiModel>
     */
    public function getAllAiModelsByOrganizationID(int $organizationID): Collection
    {
        return AiModel::where('organization_id', $organizationID)->get();
    }

    public function create(array $data): AiModel
    {
        return AiModel::create($data);
    }

    public function getAiModelByID(int $id): AiModel
    {
        $data = AiModel::with(['createdBy', 'updatedBy'])->where('id', $id)->first();
        return $data;
    }

    /**
     * Assign a dataset link to an AI model.
     * Gate: Rejects links for train/validation/test/eval_benchmark roles without snapshot_id.
     *
     * @param array $data
     * @return \App\Models\AiModelDataset
     * @throws \InvalidArgumentException
     */
    public function assignDataset(array $data): AiModelDataset
    {
        return AiModelDataset::create($data);
    }
}

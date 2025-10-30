<?php

namespace App\Repositories;

use App\Models\AiModel;
use App\Models\AiModelCard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AiModelCardRepository
{
    /**
     * Get paginated AI Model Cards for a specific AI Model.
     *
     * @param int $aiModelId
     * @param int $perPage
     * @return LengthAwarePaginator<int, AiModelCard>
     */
    public function getPaginatedAiModelCards(int $aiModelId, int $perPage): LengthAwarePaginator
    {
        return AiModelCard::where('ai_model_id', $aiModelId)->paginate($perPage);
    }

    public function createAiModelCard(array $data): AiModelCard
    {
        return AiModelCard::create($data);
    }

    public function getAiModelCardById(AiModelCard $aiModelCard): ?AiModelCard
    {
        return $aiModelCard->load(['aiModel', 'aiModelVersion']);
    }

    /**
     * Update an existing AI Model Card.
     *
     * @param AiModelCard $aiModelCard
     * @param array $data
     * @return bool
     */
    public function updateAiModelCard(AiModelCard $aiModelCard, array $data): bool
    {
        return $aiModelCard->update($data);
    }
}

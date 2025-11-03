<?php

namespace App\Repositories;

use App\Models\AiModelCard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AiModelCardRepository
{
    /**
     * Get paginated AI Model Cards for a specific AI Model.
     *
     * @param int $perPage
     * @return LengthAwarePaginator<int, AiModelCard>
     */
    public function getPaginatedAiModelCardsByOrganizationID(int $organizationId, int $perPage): LengthAwarePaginator
    {
        return AiModelCard::where('organization_id', $organizationId)->paginate($perPage);
    }

    public function createAiModelCard(array $data): AiModelCard
    {
        return AiModelCard::create($data);
    }

    public function getAiModelCardById(AiModelCard $aiModelCard): ?AiModelCard
    {
        return $aiModelCard->load('aiModelVersion');
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

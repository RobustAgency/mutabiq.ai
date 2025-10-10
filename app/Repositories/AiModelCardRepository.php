<?php
namespace App\Repositories;
use App\Models\AiModelCard;

class AiModelCardRepository
{
    public function createAiModelCard(array $data): AiModelCard
    {
        return AiModelCard::create($data);
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
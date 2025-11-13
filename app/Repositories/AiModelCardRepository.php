<?php

namespace App\Repositories;

use App\Models\AiModelCard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AiModelCardRepository
{
    /**
     * Get paginated AI Model Cards for a specific AI Model.
     *
     * @param array<string, mixed> $filter
     * @return LengthAwarePaginator<int, AiModelCard>
     */
    public function getFilteredAiModelCards(array $filter = []): LengthAwarePaginator
    {
        $query = AiModelCard::query();

        if (isset($filter['organization_id'])) {
            $query->where('organization_id', $filter['organization_id']);
        }
        if (isset($filter['creator_role'])) {
            $query->where('creator_role', $filter['creator_role']);
        }
        if (isset($filter['status'])) {
            $query->where('status', $filter['status']);
        }
        if (isset($filter['format'])) {
            $query->where('format', $filter['format']);
        }
        if (isset($filter['publication_status'])) {
            $query->where('publication_status', $filter['publication_status']);
        }

        if (! empty($filter['owner'])) {
            $query->whereHas('ownerStakeholder', function ($q) use ($filter) {
                $q->where('display_name', 'like', '%' . $filter['owner'] . '%');
            });
        }

        if (! empty($filter['from'])) {
            $query->whereDate('created_at', '>=', $filter['from']);
        }
        if (! empty($filter['to'])) {
            $query->whereDate('created_at', '<=', $filter['to']);
        }


        return $query->paginate($filter['per_page'] ?? 15);
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

<?php

namespace App\Repositories;

use App\Models\AiModel;
use Illuminate\Pagination\LengthAwarePaginator;

class AiModelRepository
{
    /**
     * Get all AI models by organization ID.
     *
     * @return LengthAwarePaginator<int, AiModel>
     */
    public function getFilteredAiModels(array $filters = []): LengthAwarePaginator
    {
        $query = AiModel::query();

        if (! empty($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        $query->when(! empty($filters['from']), function ($query) use ($filters) {
            $query->whereDate('created_at', '>=', $filters['from']);
        });

        $query->when(! empty($filters['to']), function ($query) use ($filters) {
            $query->whereDate('created_at', '<=', $filters['to']);
        });

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): AiModel
    {
        return AiModel::create($data);
    }
}

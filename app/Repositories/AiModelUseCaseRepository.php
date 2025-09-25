<?php

namespace App\Repositories;

use App\Models\AiModelUseCase;
use Illuminate\Pagination\LengthAwarePaginator;

class AiModelUseCaseRepository
{

    /**
     * Get filtered AI model use cases with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator<int, AiModelUseCase>
     */
    public function getFilteredAiModelUseCases(array $filters = []): LengthAwarePaginator
    {
        $query = AiModelUseCase::query();

        $query->when(! empty($filters['title']), function ($query) use ($filters) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        });

        $query->when(! empty($filters['status']), function ($query) use ($filters) {
            $query->where('status', $filters['status']);
        });

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    public function createAiModelUseCase(array $data): AiModelUseCase
    {
        return AiModelUseCase::create($data);
    }
}

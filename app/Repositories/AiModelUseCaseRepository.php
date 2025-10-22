<?php

namespace App\Repositories;

use App\Models\AiModelUseCase;
use App\Models\User;

class AiModelUseCaseRepository
{
    /**
     * Retrieve AI Model Use Case associations with optional filtering and pagination.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, AiModelUseCase>
     */
    public function getFilteredAiModelUseCases(array $filters)
    {
        $query = AiModelUseCase::with(['aiModel', 'useCase', 'aiModelVersion']);

        if (isset($filters['ai_model_id'])) {
            $query->where('ai_model_id', $filters['ai_model_id']);
        }

        $per_page = $filters['per_page'] ?? 10;

        return $query->paginate($per_page);
    }

    public function createAiModelUseCase(User $user, array $data): AiModelUseCase
    {
        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;
        return AiModelUseCase::create($data);
    }

    public function updateAiModelUseCase(AiModelUseCase $aiModelUseCase, User $user, array $data): bool
    {
        $data['updated_by'] = $user->id;
        return $aiModelUseCase->update($data);
    }
}

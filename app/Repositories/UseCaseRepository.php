<?php

namespace App\Repositories;

use App\Models\UseCase;
use Illuminate\Pagination\LengthAwarePaginator;

class UseCaseRepository
{

    /**
     * Get filtered use cases with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator<int, UseCase>
     */
    public function getFilteredUseCases(array $filters = []): LengthAwarePaginator
    {
        $query = UseCase::query();

        $query->when(! empty($filters['name']), function ($query) use ($filters) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        });

        $query->when(! empty($filters['status']), function ($query) use ($filters) {
            $query->where('status', $filters['status']);
        });

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    public function createUseCase(array $data): UseCase
    {
        return UseCase::create($data);
    }
}

<?php

namespace App\Repositories;

use App\Models\UseCase;
use Illuminate\Pagination\LengthAwarePaginator;

class UseCaseRepository
{
    /**
     * Get filtered use cases with optional filters.
     *
     * @return LengthAwarePaginator<int, UseCase>
     */
    public function getFilteredUseCases(array $filters = []): LengthAwarePaginator
    {
        $query = UseCase::query();

        $query->when(! empty($filters['organization_id']), function ($query) use ($filters) {
            $query->where('organization_id', $filters['organization_id']);
        });

        $query->when(! empty($filters['preliminary_risk_level']), function ($query) use ($filters) {
            $query->where('preliminary_risk_level', $filters['preliminary_risk_level']);
        });

        $query->when(! empty($filters['status']), function ($query) use ($filters) {
            $query->where('status', $filters['status']);
        });

        $query->when(! empty($filters['business_domain']), function ($query) use ($filters) {
            $query->where('business_domain', $filters['business_domain']);
        });

        $query->when(! empty($filters['owner']), function ($query) use ($filters) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('businessOwner', function ($subQuery) use ($filters) {
                    $subQuery->where('display_name', 'like', '%'.$filters['owner'].'%');
                })->orWhereHas('technicalOwner', function ($subQuery) use ($filters) {
                    $subQuery->where('display_name', 'like', '%'.$filters['owner'].'%');
                });
            });
        });

        $query->when(! empty($filters['from']), function ($query) use ($filters) {
            $query->whereDate('created_at', '>=', $filters['from']);
        });

        $query->when(! empty($filters['to']), function ($query) use ($filters) {
            $query->whereDate('created_at', '<=', $filters['to']);
        });

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    public function createUseCase(array $data): UseCase
    {
        $stakeholderIds = $data['stakeholder_ids'] ?? [];
        unset($data['stakeholder_ids']);

        $useCase = UseCase::create($data);

        $useCase->stakeholders()->attach($stakeholderIds);

        return $useCase;
    }
}

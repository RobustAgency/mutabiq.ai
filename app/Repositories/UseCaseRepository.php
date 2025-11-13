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

        $query->when(! empty($filters['organization_id']), function ($query) use ($filters) {
            $query->where('organization_id', $filters['organization_id']);
        });

        $query->when(! empty($filters['risk_level']), function ($query) use ($filters) {
            $query->where('risk_level', $filters['risk_level']);
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
                    $subQuery->where('display_name', 'like', '%' . $filters['owner'] . '%');
                })->orWhereHas('technicalOwner', function ($subQuery) use ($filters) {
                    $subQuery->where('display_name', 'like', '%' . $filters['owner'] . '%');
                });
            });
        });

        $query->when(! empty($filters['roi_assessment']), function ($query) use ($filters) {
            $query->where('roi_assessment', $filters['roi_assessment']);
        });

        $query->when(! empty($filters['risk_assessment']), function ($query) use ($filters) {
            $query->where('risk_assessment', $filters['risk_assessment']);
        });

        $query->when(! empty($filters['data_assessment']), function ($query) use ($filters) {
            $query->where('data_assessment', $filters['data_assessment']);
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
        return UseCase::create($data);
    }
}

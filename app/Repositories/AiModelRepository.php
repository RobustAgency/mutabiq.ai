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

        if (! empty($filters['status'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('operational_status', $filters['status'])
                    ->orWhere('business_status', $filters['status']);
            });
        }

        if (! empty($filters['ownership_type'])) {
            $query->where('ownership_type', $filters['ownership_type']);
        }

        if (! empty($filters['regulatory_risk_classification'])) {
            $query->where('regulatory_risk_classification', $filters['regulatory_risk_classification']);
        }

        $query->when(! empty($filters['owner']), function ($query) use ($filters) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('ownerStakeholder', function ($subQuery) use ($filters) {
                    $subQuery->where('display_name', 'like', '%'.$filters['owner'].'%');
                })->orWhereHas('sourceOrgStakeholder', function ($subQuery) use ($filters) {
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

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): AiModel
    {
        return AiModel::create($data);
    }

    public function getAiModelByID(int $id): AiModel
    {
        $data = AiModel::with(['createdBy', 'updatedBy'])->where('id', $id)->first();

        return $data;
    }
}

<?php

namespace App\Repositories;

use App\Models\KriIndicator;
use Illuminate\Pagination\LengthAwarePaginator;

class KriIndicatorRepository
{
    /**
     * Get filtered KRI Indicators with pagination.
     *
     * @return LengthAwarePaginator<int, KriIndicator>
     */
    public function getFilteredKriIndicators(array $filters): LengthAwarePaginator
    {
        $query = KriIndicator::query();

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['frequency'])) {
            $query->where('frequency', $filters['frequency']);
        }

        if (isset($filters['directionality'])) {
            $query->where('directionality', $filters['directionality']);
        }

        if (isset($filters['collection_method'])) {
            $query->where('collection_method', $filters['collection_method']);
        }

        if (isset($filters['action_on_breach'])) {
            $query->where('action_on_breach', $filters['action_on_breach']);
        }

        $perPage = $filters['per_page'] ?? 15;

        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);

    }

    public function createKriIndicator(array $data): KriIndicator
    {
        return KriIndicator::create($data);
    }

    public function updateKriIndicator(KriIndicator $kriIndicator, array $data): KriIndicator
    {
        $kriIndicator->update($data);

        return $kriIndicator;
    }

    public function deleteKriIndicator(KriIndicator $kriIndicator): void
    {
        $kriIndicator->delete();
    }
}

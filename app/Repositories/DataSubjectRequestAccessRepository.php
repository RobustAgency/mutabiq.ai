<?php

namespace App\Repositories;

use App\Models\DataSubjectRequestAccess;
use Illuminate\Pagination\LengthAwarePaginator;

class DataSubjectRequestAccessRepository
{
    /**
     * Get paginated list of data subject request accesses with specified filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, DataSubjectRequestAccess>
     */
    public function getFilteredDataSubjectRequestAccesses(array $filters = []): LengthAwarePaginator
    {
        $query = DataSubjectRequestAccess::query();

        if (isset($filters['status']) && ! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['request_type']) && ! empty($filters['request_type'])) {
            $query->where('request_type', $filters['request_type']);
        }

        if (isset($filters['verification_status']) && ! empty($filters['verification_status'])) {
            $query->where('verification_status', $filters['verification_status']);
        }

        if (isset($filters['jurisdiction']) && ! empty($filters['jurisdiction'])) {
            $query->where('jurisdiction', $filters['jurisdiction']);
        }

        if (isset($filters['subject_realm']) && ! empty($filters['subject_realm'])) {
            $query->where('subject_realm', $filters['subject_realm']);
        }

        if (isset($filters['priority']) && ! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function createDataSubjectRequestAccess(array $data): DataSubjectRequestAccess
    {
        return DataSubjectRequestAccess::create($data);
    }

    public function updateDataSubjectRequestAccess(DataSubjectRequestAccess $dataSubjectRequestAccess, array $data): DataSubjectRequestAccess
    {
        $dataSubjectRequestAccess->update($data);

        return $dataSubjectRequestAccess->fresh();
    }

    public function deleteDataSubjectRequestAccess(DataSubjectRequestAccess $dataSubjectRequestAccess): bool
    {
        return (bool) $dataSubjectRequestAccess->delete();
    }
}

<?php

namespace App\Repositories;

use App\Models\AiCommittee;
use Illuminate\Pagination\LengthAwarePaginator;

class AiCommitteeRepository
{
    /**
     * Get paginated list of AI committees with specified filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, AiCommittee>
     */
    public function getFilteredCommittees(array $filters = []): LengthAwarePaginator
    {
        $query = AiCommittee::query();

        if (isset($filters['type']) && ! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['cadence']) && ! empty($filters['cadence'])) {
            $query->where('cadence', $filters['cadence']);
        }

        if (isset($filters['active'])) {
            $query->where('active', (bool) $filters['active']);
        }

        if (isset($filters['name']) && ! empty($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new AI committee.
     */
    public function createCommittee(array $data): AiCommittee
    {
        return AiCommittee::create($data);
    }

    /**
     * Update an existing AI committee.
     */
    public function updateCommittee(AiCommittee $committee, array $data): AiCommittee
    {
        $committee->update($data);

        return $committee->fresh();
    }

    /**
     * Delete an AI committee.
     */
    public function deleteCommittee(AiCommittee $committee): bool
    {
        return (bool) $committee->delete();
    }
}

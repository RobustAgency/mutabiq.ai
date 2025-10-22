<?php

namespace App\Repositories;

use App\Models\Stakeholder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StakeholderRepository
{
    /**
     * Get filtered stakeholders with pagination.
     *
     * @param array $filters
     * @return LengthAwarePaginator<int, Stakeholder>
     */
    public function getFilteredStakeholders(array $filters = []): LengthAwarePaginator
    {
        $query = Stakeholder::query();

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    public function create(array $stakeholderData): Stakeholder
    {
        return Stakeholder::create($stakeholderData);
    }

    public function update(Stakeholder $stakeholder, array $stakeholderData): Stakeholder
    {
        $stakeholder->update($stakeholderData);

        return $stakeholder;
    }
}

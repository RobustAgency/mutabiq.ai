<?php

namespace App\Repositories;

use App\Models\Stakeholder;
use Illuminate\Database\Eloquent\Builder;
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

        if (!empty($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $query = $this->search($query, $filters['search']);
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

    /**
     * Narrow query by search key.
     *
     * @param  Builder<\App\Models\Stakeholder>  $query
     * @param  string  $key
     * @return Builder<\App\Models\Stakeholder>
     */
    private function search(Builder $query, string $key): Builder
    {
        return $query->where(function (Builder $q) use ($key) {
            $q->where('display_name', 'like', "%{$key}%")
                ->orWhere('legal_name', 'like', "%{$key}%")
                ->orWhere('email', 'like', "%{$key}%");
        });
    }
}

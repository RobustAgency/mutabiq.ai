<?php

namespace App\Repositories;

use App\Models\Stakeholder;
use Illuminate\Pagination\LengthAwarePaginator;

class StakeholderRepository
{
    /**
     * Get filtered stakeholders with pagination.
     *
     * @return LengthAwarePaginator<int, Stakeholder>
     */
    public function getFilteredStakeholders(array $filters = []): LengthAwarePaginator
    {
        $query = Stakeholder::query();

        if (! empty($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['name'])) {
            $query->where('display_name', 'like', '%'.$filters['name'].'%');
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
     * Get stakeholder statistics for an organization.
     */
    public function getStatistics(int $organizationId): array
    {
        $stats = Stakeholder::where('organization_id', $organizationId)
            ->selectRaw('
            COUNT(*) as total_count,
            SUM(classification = "internal") as internal_count,
            SUM(classification = "external") as external_count
        ')
            ->first();

        $statsArray = $stats->toArray();

        return [
            'total_count' => (int) ($statsArray['total_count'] ?? 0),
            'internal_count' => (int) ($statsArray['internal_count'] ?? 0),
            'external_count' => (int) ($statsArray['external_count'] ?? 0),
        ];
    }
}

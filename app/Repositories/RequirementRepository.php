<?php

namespace App\Repositories;

use App\Models\Requirement;
use Illuminate\Pagination\LengthAwarePaginator;

class RequirementRepository
{
    /**
     * Get paginated list of requirements with specified filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Requirement>
     */
    public function getFilteredRequirements(array $filters = []): LengthAwarePaginator
    {
        $query = Requirement::withCount('frameworks', 'controls');

        $query->when(! empty($filters['category']), function ($query) use ($filters) {
            $query->where('category', $filters['category']);
        });

        $query->when(! empty($filters['priority']), function ($query) use ($filters) {
            $query->where('priority', $filters['priority']);
        });

        $query->when(! empty($filters['effective_from']), function ($query) use ($filters) {
            $query->whereDate('effective_from', '>=', $filters['effective_from']);
        });

        $query->when(! empty($filters['effective_to']), function ($query) use ($filters) {
            $query->whereDate('effective_to', '<=', $filters['effective_to']);
        });

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    public function createForAdmin(array $requirementData): Requirement
    {
        $requirement = Requirement::create($requirementData);
        if (isset($requirementData['framework_ids'])) {
            $requirement->frameworks()->sync($requirementData['framework_ids']);
        }

        return $requirement;
    }

    public function update(Requirement $requirement, array $requirementData): Requirement
    {
        $frameworkIds = $requirementData['framework_ids'] ?? null;
        unset($requirementData['framework_ids']);

        $requirement->update($requirementData);

        if ($frameworkIds !== null) {
            $requirement->frameworks()->sync($frameworkIds);
        }

        return $requirement;
    }
}

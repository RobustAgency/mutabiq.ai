<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Requirement;
use Illuminate\Pagination\LengthAwarePaginator;

class RequirementRepository
{
    /**
     * Get paginated list of requirements with specified filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Requirement>
     */
    public function getFilteredRequirements(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Requirement::where('user_id', $user->id)->withCount('frameworks', 'controls');

        $query->when(! empty($filters['name']), function ($query) use ($filters) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        });

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    public function createForAdmin(User $user, array $requirementData): Requirement
    {
        $requirementData['user_id'] = $user->id;

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

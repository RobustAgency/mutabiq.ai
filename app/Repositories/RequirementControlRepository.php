<?php

namespace App\Repositories;

use App\Models\RequirementControl;
use Illuminate\Pagination\LengthAwarePaginator;

class RequirementControlRepository
{
    /**
     * Get paginated list of requirement controls with specified filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, RequirementControl>
     */
    public function getFilteredRequirementControls(array $filters = []): LengthAwarePaginator
    {
        $query = RequirementControl::with(['requirement', 'control', 'user']);

        $query->when(! empty($filters['requirement_id']), function ($query) use ($filters) {
            $query->where('requirement_id', $filters['requirement_id']);
        });

        $query->when(! empty($filters['control_id']), function ($query) use ($filters) {
            $query->where('control_id', $filters['control_id']);
        });

        $query->when(! empty($filters['coverage']), function ($query) use ($filters) {
            $query->where('coverage', $filters['coverage']);
        });

        $query->when(! empty($filters['review_status']), function ($query) use ($filters) {
            $query->where('review_status', $filters['review_status']);
        });

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new requirement control mapping.
     */
    public function createRequirementControl(array $data): RequirementControl
    {
        return RequirementControl::create($data);
    }

    /**
     * Update an existing requirement control mapping.
     */
    public function updateRequirementControl(RequirementControl $requirementControl, array $data): RequirementControl
    {
        $requirementControl->update($data);

        return $requirementControl;
    }

    /**
     * Delete a requirement control mapping.
     */
    public function deleteRequirementControl(RequirementControl $requirementControl): bool
    {
        return $requirementControl->delete();
    }
}

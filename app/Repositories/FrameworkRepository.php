<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Framework;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FrameworkRepository
{
    /**
     * Get paginated list of frameworks with specified filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Framework>
     */
    public function getFilteredFrameworks(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Framework::where('user_id', $user->id)->with('media')->withCount('controls', 'requirements');

        $query = $this->applyFilters($query, $filters);

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    public function createForAdmin(User $user, array $frameworkData): Framework
    {
        $frameworkData['user_id'] = $user->id;

        return Framework::create($frameworkData);
    }

    public function update(Framework $framework, array $frameworkData): Framework
    {
        $framework->update($frameworkData);

        return $framework;
    }

    /**
     * Get available frameworks for user with optional filters.
     * 
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Framework>
     */
    public function getPublishedFrameworks(array $filters = []): Collection
    {
        $query = Framework::where('is_published', true)
            ->with('media')
            ->withCount('controls', 'requirements');

        $query = $this->applyFilters($query, $filters);

        return $query->get();
    }

    public function getFrameworkByID(int $id): Framework
    {
        return Framework::with('media')->withCount('controls', 'requirements')->find($id);
    }

    private function applyFilters($query, array $filters)
    {
        $query->when(! empty($filters['name']), function ($query) use ($filters) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        });

        $query->when(! empty($filters['status']), function ($query) use ($filters) {
            $query->where('is_published', $filters['status']);
        });

        $query->when(! empty($filters['type']), function ($query) use ($filters) {
            $query->where('type', 'like', '%' . $filters['type'] . '%');
        });

        $query->when(! empty($filters['authority_publisher']), function ($query) use ($filters) {
            $query->where('authority_publisher', 'like', '%' . $filters['authority_publisher'] . '%');
        });

        $query->when(! empty($filters['binding_level']), function ($query) use ($filters) {
            $query->where('binding_level', 'like', '%' . $filters['binding_level'] . '%');
        });

        $query->when(! empty($filters['sector_applicability']), function ($query) use ($filters) {
            $query->where('sector_applicability', 'like', '%' . $filters['sector_applicability'] . '%');
        });

        $query->when(! empty($filters['risk_class_coverage']), function ($query) use ($filters) {
            $query->where('risk_class_coverage', 'like', '%' . $filters['risk_class_coverage'] . '%');
        });

        $query->when(! empty($filters['certification_attestation']), function ($query) use ($filters) {
            $query->where('certification_attestation', 'like', '%' . $filters['certification_attestation'] . '%');
        });

        $query->when(! empty($filters['assessment_mode']), function ($query) use ($filters) {
            $query->where('assessment_mode', 'like', '%' . $filters['assessment_mode'] . '%');
        });
        return $query;
    }
}

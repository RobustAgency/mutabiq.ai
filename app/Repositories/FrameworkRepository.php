<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Framework;
use Illuminate\Database\Eloquent\Builder;
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
     * @return \Illuminate\Database\Eloquent\Collection<int, Framework>
     */
    public function getPublishedFrameworks(array $filters = []): Collection
    {
        $query = Framework::where('effective_date', '<=', now())
            ->with('media')
            ->withCount('controls', 'requirements');

        $query = $this->applyFilters($query, $filters);

        return $query->get();
    }

    public function getFrameworkByID(int $id): Framework
    {
        return Framework::with('media')->withCount('controls', 'requirements')->find($id);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Framework>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Framework>
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        $query->when(! empty($filters['name']), function ($query) use ($filters) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        });

        $query->when(! empty($filters['status']), function ($query) use ($filters) {
            $query->where('status', $filters['status']);
        });

        $query->when(! empty($filters['effective_date_from']), function ($query) use ($filters) {
            $query->where('effective_date', '>=', $filters['effective_date_from']);
        });

        $query->when(! empty($filters['effective_date_to']), function ($query) use ($filters) {
            $query->where('effective_date', '<=', $filters['effective_date_to']);
        });

        return $query;
    }
}

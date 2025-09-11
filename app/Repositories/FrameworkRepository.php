<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Framework;
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

        $query->when(! empty($filters['name']), function ($query) use ($filters) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        });

        $query->when(! empty($filters['status']), function ($query) use ($filters) {
            $query->where('is_published', $filters['status']);
        });

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
}

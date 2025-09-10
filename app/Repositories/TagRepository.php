<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class TagRepository
{
    /**
     * Get paginated list of tags with specified filters.
     *
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Tag>
     */
    public function getFilteredTagsForAdmin(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Tag::where('user_id', $user->id);

        $query->when(! empty($filters['term']), function ($query) use ($filters) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['term']}%")
                    ->orWhere('group', 'like', "%{$filters['term']}%");
            });
        });

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new tag for the given user.
     */
    public function createForUser(User $user, array $tagData): Tag
    {
        $tagData['user_id'] = $user->id;

        return Tag::updateOrCreate(
            [
                'user_id' => $user->id,
                'group' => $tagData['group'],
            ],
            ['name' => $tagData['name']]
        );
    }
}

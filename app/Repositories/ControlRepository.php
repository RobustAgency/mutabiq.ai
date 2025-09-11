<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Control;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ControlRepository
{
    /**
     * Get paginated list of controls with specified filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Control>
     */
    public function getFilteredControls(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Control::where('user_id', $user->id)->withCount('frameworks', 'requirements');

        $query->when(! empty($filters['name']), function ($query) use ($filters) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        });

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    public function createForAdmin(User $user, array $controlData): Control
    {
        $controlData['user_id'] = $user->id;

        $control = Control::create($controlData);

        $control->frameworks()->sync($controlData['framework_ids'] ?? []);
        $control->requirements()->sync($controlData['requirement_ids'] ?? []);
        $control->tags()->sync($controlData['tag_ids'] ?? []);

        return $control;
    }

    public function update(Control $control, array $data): Control
    {
        $control->update($data);

        if (isset($data['framework_ids'])) {
            $control->frameworks()->sync($data['framework_ids']);
        }
        if (isset($data['requirement_ids'])) {
            $control->requirements()->sync($data['requirement_ids']);
        }
        if (isset($data['tag_ids'])) {
            $control->tags()->sync($data['tag_ids']);
        }

        return $control;
    }
}

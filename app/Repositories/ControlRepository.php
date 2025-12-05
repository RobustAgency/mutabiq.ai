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
        $query = Control::where('user_id', $user->id);

        $query->when(! empty($filters['name']), function ($query) use ($filters) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        });

        $query->when(! empty($filters['status']), function ($query) use ($filters) {
            $query->where('status', $filters['status']);
        });

        $query->when(! empty($filters['testing_method']), function ($query) use ($filters) {
            $query->where('testing_method', $filters['testing_method']);
        });

        $query->when(! empty($filters['testing_frequency']), function ($query) use ($filters) {
            $query->where('testing_frequency', $filters['testing_frequency']);
        });

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    public function createForAdmin(User $user, array $controlData): Control
    {
        $controlData['user_id'] = $user->id;

        $control = Control::create($controlData);

        return $control;
    }

    public function update(Control $control, array $data): Control
    {
        $control->update($data);

        return $control;
    }
}

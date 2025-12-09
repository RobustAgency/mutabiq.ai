<?php

namespace App\Repositories;

use App\Models\RecordOfProcessingActivity;
use Illuminate\Pagination\LengthAwarePaginator;

class RecordOfProcessingActivityRepository
{
    /**
     * Get paginated list of processing activities with specified filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, RecordOfProcessingActivity>
     */
    public function getFilteredActivities(array $filters = []): LengthAwarePaginator
    {
        $query = RecordOfProcessingActivity::query();

        $query->when(! empty($filters['status']), function ($query) use ($filters) {
            $query->where('status', $filters['status']);
        });

        $query->when(! empty($filters['owner_team']), function ($query) use ($filters) {
            $query->where('owner_team', $filters['owner_team']);
        });
        $query->when(isset($filters['from']), function ($query) use ($filters) {
            $query->where('created_at', '>=', $filters['from']);
        });

        $query->when(isset($filters['to']), function ($query) use ($filters) {
            $query->where('created_at', '<=', $filters['to']);
        });

        $query->orderBy('created_at', 'desc');

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Create a new processing activity record.
     * Version is automatically set to 1 by the model's boot method.
     */
    public function createActivity(array $data): RecordOfProcessingActivity
    {
        // Remove version from data if provided - it will be set by boot method
        unset($data['version']);

        return RecordOfProcessingActivity::create($data);
    }

    /**
     * Update an existing processing activity record.
     * Version is automatically incremented by the model's boot method.
     */
    public function updateActivity(RecordOfProcessingActivity $activity, array $data): RecordOfProcessingActivity
    {
        $activity->update($data);

        return $activity->fresh();
    }

    /**
     * Delete a processing activity record.
     */
    public function deleteActivity(RecordOfProcessingActivity $activity): bool
    {
        return $activity->delete();
    }
}

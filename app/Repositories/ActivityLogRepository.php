<?php

namespace App\Repositories;

use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityLogRepository
{
    /**
     * Get paginated activity logs.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, ActivityLog>
     */
    public function getFilteredActivityLog(array $filters = []): LengthAwarePaginator
    {
        $query = ActivityLog::with(['user', 'organization']);

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['actable_type'])) {
            $query->where('actable_type', $filters['actable_type']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a new activity log.
     *
     * @param  array<string, mixed>  $data
     */
    public function createActivityLog(array $data): ActivityLog
    {
        return ActivityLog::create($data);
    }
}

<?php

namespace App\Repositories;

use App\Models\ArtifactAccessLog;
use Illuminate\Pagination\LengthAwarePaginator;

class ArtifactAccessLogRepository
{
    /**
     * Get paginated list of artifact access logs
     *
     * @param array $filters
     * @return LengthAwarePaginator <int, ArtifactAccessLog>
     */
    public function getFilteredArtifactAccessLogs(array $filters = []): LengthAwarePaginator
    {
        $query = ArtifactAccessLog::query()
            ->with(['artifact.aiModelVersion.aiModel', 'accessorStakeholder']);

        // Filter by artifact_id
        if (isset($filters['artifact_id'])) {
            $query->where('artifact_id', $filters['artifact_id']);
        }

        // Filter by accessor_stakeholder_id
        if (isset($filters['accessor_stakeholder_id'])) {
            $query->where('accessor_stakeholder_id', $filters['accessor_stakeholder_id']);
        }

        // Filter by action
        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        // Filter by context
        if (isset($filters['context'])) {
            $query->where('context', $filters['context']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('ts', 'desc')->paginate($perPage);
    }

    /**
     * Create a new artifact access log entry
     *
     * @param array $data
     * @return ArtifactAccessLog
     */
    public function createArtifactAccessLog(array $data): ArtifactAccessLog
    {
        return ArtifactAccessLog::create($data);
    }
}

<?php

namespace App\Repositories;

use App\Models\AiIncident;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AiIncidentRepository
{
    /**
     * Get paginated AI incidents.
     * @param array<string, mixed> $filters
     *
     * @return LengthAwarePaginator<int, AiIncident>
     */
    public function getFilteredAiIncident(array $filters = []): LengthAwarePaginator
    {
        $query = AiIncident::query();

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (isset($filters['stage'])) {
            $query->where('stage', $filters['stage']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
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
     * Create a new AI incident.
     *
     * @param array<string, mixed> $data
     */
    public function createAiIncident(array $data): AiIncident
    {
        return AiIncident::create($data);
    }

    /**
     * Update an existing AI incident.
     *
     * @param array<string, mixed> $data
     */
    public function updateAiIncident(AiIncident $aiIncident, array $data): AiIncident
    {
        $aiIncident->update($data);
        return $aiIncident->fresh();
    }

    /**
     * Delete an AI incident.
     */
    public function deleteAiIncident(AiIncident $aiIncident): bool
    {
        return $aiIncident->delete();
    }
}

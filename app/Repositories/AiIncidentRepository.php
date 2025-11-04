<?php

namespace App\Repositories;

use App\Models\AiIncident;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AiIncidentRepository
{
    /**
     * Get paginated AI incidents.
     *
     * @return LengthAwarePaginator<int, AiIncident>
     */
    public function getPaginatedAiIncidents(int $organizationID, int $perPage = 15): LengthAwarePaginator
    {
        return AiIncident::query()
            ->where('organization_id', $organizationID)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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

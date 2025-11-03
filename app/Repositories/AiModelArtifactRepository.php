<?php

namespace App\Repositories;

use App\Models\AiModelArtifact;
use Illuminate\Pagination\LengthAwarePaginator;

class AiModelArtifactRepository
{
    /**
     * Retrieve paginated AI model artifacts for a given organization.
     *
     * @param int $organization_id
     * @param int $perPage
     * @return LengthAwarePaginator<int, AiModelArtifact>
     */
    public function getPaginatedArtifacts(int $organization_id, int $perPage = 15): LengthAwarePaginator
    {
        $query = AiModelArtifact::query();
        $query->where('organization_id', $organization_id);

        return $query->paginate($perPage);
    }

    public function createAiModelArtifact(array $data): AiModelArtifact
    {
        return AiModelArtifact::create($data);
    }

    public function updateAiModelArtifact(AiModelArtifact $aiModelArtifact, array $data): bool
    {
        return $aiModelArtifact->update($data);
    }
}

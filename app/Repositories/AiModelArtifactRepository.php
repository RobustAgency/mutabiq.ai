<?php

namespace App\Repositories;

use App\Models\AiModelArtifact;
use Illuminate\Pagination\LengthAwarePaginator;

class AiModelArtifactRepository
{
    /**
     * Retrieve paginated AI model artifacts for a given organization.
     *
     * @param array<string, mixed> $filter
     * @return LengthAwarePaginator<int, AiModelArtifact>
     */
    public function getFilteredAiArtifacts(array $filter = []): LengthAwarePaginator
    {
        $query = AiModelArtifact::query();

        if (isset($filter['organization_id'])) {
            $query->where('organization_id', $filter['organization_id']);
        }

        if (isset($filter['artifact_type'])) {
            $query->where('artifact_type', $filter['artifact_type']);
        }

        if (isset($filter['name'])) {
            $query->where('name', 'like', '%' . $filter['name'] . '%');
        }

        return $query->paginate($filter['per_page'] ?? 15);
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

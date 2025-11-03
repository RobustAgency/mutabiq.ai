<?php

namespace App\Repositories;

use App\Models\AiModelVersion;
use Illuminate\Database\Eloquent\Collection;

class AiModelVersionRepository
{
    /**
     * Retrieve all AI model versions, optionally filtered by AI model ID and organization.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection<int, AiModelVersion>
     */
    public function getFilteredAiModelVersions(array $filters = []): Collection
    {
        $query = AiModelVersion::query();

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['ai_model_id'])) {
            $query->where('ai_model_id', $filters['ai_model_id']);
        }

        return $query->get();
    }

    public function create(array $data): AiModelVersion
    {
        return AiModelVersion::create($data);
    }

    public function getAiModelVersionByID(int $id): ?AiModelVersion
    {
        return AiModelVersion::find($id);
    }

    public function updateAiModelVersion(AiModelVersion $aiModelVersion, array $data): bool
    {
        return $aiModelVersion->update($data);
    }
}

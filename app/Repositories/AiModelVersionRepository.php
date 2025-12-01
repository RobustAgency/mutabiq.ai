<?php

namespace App\Repositories;

use App\Models\AiModelVersion;
use Illuminate\Pagination\LengthAwarePaginator;

class AiModelVersionRepository
{
    /**
     * Retrieve all AI model versions, optionally filtered by AI model ID and organization.
     *
     * @return LengthAwarePaginator<int, AiModelVersion>
     */
    public function getFilteredAiModelVersions(array $filters = []): LengthAwarePaginator
    {
        $query = AiModelVersion::query();

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['ai_model_id'])) {
            $query->where('ai_model_id', $filters['ai_model_id']);
        }

        if (isset($filters['version_type'])) {
            $query->where('version_type', $filters['version_type']);
        }
        if (isset($filters['deployment_status'])) {
            $query->where('deployment_status', $filters['deployment_status']);
        }
        if (isset($filters['lifecycle_stage'])) {
            $query->where('lifecycle_stage', $filters['lifecycle_stage']);
        }
        if (isset($filters['release_role'])) {
            $query->where('release_role', $filters['release_role']);
        }
        if (isset($filters['source_type'])) {
            $query->where('source_type', $filters['source_type']);
        }

        $query->when(! empty($filters['from']), function ($query) use ($filters) {
            $query->whereDate('created_at', '>=', $filters['from']);
        });

        $query->when(! empty($filters['to']), function ($query) use ($filters) {
            $query->whereDate('created_at', '<=', $filters['to']);
        });

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Create a new AI model version.
     *
     * @param  array<string, mixed>  $data
     */
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

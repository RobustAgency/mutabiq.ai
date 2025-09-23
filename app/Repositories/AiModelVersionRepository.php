<?php
namespace App\Repositories;

use App\Models\AiModelVersion;

class AiModelVersionRepository
{
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
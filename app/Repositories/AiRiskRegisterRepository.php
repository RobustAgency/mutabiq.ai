<?php

namespace App\Repositories;

use App\Models\AiRiskRegister;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AiRiskRegisterRepository
{
    /**
     * Get paginated AI risk register entries.
     *
     * @return LengthAwarePaginator<int, AiRiskRegister>
     */
    public function getPaginatedAiRiskRegister(int $organizationID, int $perPage = 15): LengthAwarePaginator
    {
        return AiRiskRegister::with(['aiModel', 'riskOwner'])
            ->where('organization_id', $organizationID)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new AI risk register entry.
     *
     * @param  array<string, mixed>  $data
     */
    public function createAiRiskRegister(array $data): AiRiskRegister
    {
        return AiRiskRegister::create($data);
    }

    /**
     * Update an existing AI risk register entry.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateAiRiskRegister(AiRiskRegister $aiRiskRegister, array $data): AiRiskRegister
    {
        $aiRiskRegister->update($data);

        return $aiRiskRegister->fresh();
    }

    /**
     * Delete an AI risk register entry.
     */
    public function deleteAiRiskRegister(AiRiskRegister $aiRiskRegister): bool
    {
        return $aiRiskRegister->delete();
    }

    /**
     * Get an AI risk register entry by ID with relationships.
     */
    public function getAiRiskRegisterByID(AiRiskRegister $aiRiskRegister): ?AiRiskRegister
    {
        return $aiRiskRegister->load([
            'aiModel',
            'aiModelVersion',
            'useCase',
            'riskOwner',
            'aiRiskMethodology',
        ]);
    }
}

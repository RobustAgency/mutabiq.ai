<?php

namespace App\Repositories;

use App\Models\AiRiskTreatment;
use Illuminate\Pagination\LengthAwarePaginator;

class AiRiskTreatmentRepository
{
    /**
     * Get paginated AI risk treatments based on filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, AiRiskTreatment>
     */
    public function getFilteredAiRiskTreatments(array $filters = []): LengthAwarePaginator
    {
        $query = AiRiskTreatment::query();

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['treatment_type'])) {
            $query->where('treatment_type', $filters['treatment_type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = $filters['per_page'] ?? 15;

        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Create a new AI risk treatment.
     *
     * @param  array<string, mixed>  $data
     */
    public function createAiRiskTreatment(array $data): AiRiskTreatment
    {
        return AiRiskTreatment::create($data);
    }

    /**
     * Update an existing AI risk treatment.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateAiRiskTreatment(AiRiskTreatment $treatment, array $data): bool
    {
        return $treatment->update($data);
    }

    /**
     * Delete an AI risk treatment.
     */
    public function deleteAiRiskTreatment(AiRiskTreatment $treatment): bool
    {
        return $treatment->delete();
    }
}

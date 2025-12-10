<?php

namespace App\Repositories;

use App\Models\DataProtectionImpactAssessment;
use Illuminate\Pagination\LengthAwarePaginator;

class DataProtectionImpactAssessmentRepository
{
    /**
     * Get paginated list of DPIA records with filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, DataProtectionImpactAssessment>
     */
    public function getFilteredDataProtectionImpactAssessments(array $filters = []): LengthAwarePaginator
    {
        $query = DataProtectionImpactAssessment::query();

        $query->when(! empty($filters['name']), function ($query) use ($filters) {
            $query->where('dpia_name', 'like', '%'.$filters['name'].'%');
        });

        $query->when(! empty($filters['status']), function ($query) use ($filters) {
            $query->where('status', $filters['status']);
        });

        $query->when(! empty($filters['stage']), function ($query) use ($filters) {
            $query->where('stage', $filters['stage']);
        });

        $query->when(! empty($filters['risk_level']), function ($query) use ($filters) {
            $query->where('risk_level', $filters['risk_level']);
        });

        $query->orderBy('created_at', 'desc');

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Create a new DPIA.
     */
    public function createDataProtectionImpactAssessment(array $data): DataProtectionImpactAssessment
    {
        return DataProtectionImpactAssessment::create($data);
    }

    /**
     * Update an existing DPIA.
     */
    public function updateDataProtectionImpactAssessment(DataProtectionImpactAssessment $dataProtectionImpactAssessment, array $data): DataProtectionImpactAssessment
    {
        $dataProtectionImpactAssessment->update($data);

        return $dataProtectionImpactAssessment->fresh();
    }

    /**
     * Delete a DPIA.
     */
    public function deleteDataProtectionImpactAssessment(DataProtectionImpactAssessment $dataProtectionImpactAssessment): bool
    {
        return $dataProtectionImpactAssessment->delete();
    }
}

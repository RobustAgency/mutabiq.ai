<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Agreement;
use Illuminate\Pagination\LengthAwarePaginator;

class AgreementRepository
{
    /**
     * @return LengthAwarePaginator<int, Agreement>
     */
    public function getPaginatedAgreements(int $organizationID, int $perPage = 15): LengthAwarePaginator
    {
        return Agreement::with('vendor')
            ->where('organization_id', $organizationID)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new agreement.
     */
    public function createAgreement(array $data): Agreement
    {
        return Agreement::create($data);
    }

    /**
     * Update an existing agreement.
     */
    public function updateAgreement(Agreement $agreement, array $data): Agreement
    {
        $agreement->update($data);

        return $agreement->fresh();
    }

    /**
     * Delete an agreement.
     */
    public function deleteAgreement(Agreement $agreement): bool
    {
        return $agreement->delete();
    }

    /**
     * Get agreement statistics for an organization.
     */
    public function getStatistics(int $organizationID): array
    {
        $now = Carbon::now();
        $in90Days = $now->copy()->addDays(90);

        $query = Agreement::where('organization_id', $organizationID);

        return $query->selectRaw('
            COUNT(*) as total_agreements,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_agreements,
            SUM(CASE 
                WHEN status = ? 
                AND effective_to BETWEEN ? AND ? 
                THEN 1 ELSE 0 END) as expiring_in_90_days,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_signature_count
        ', [
            'active',
            'active',
            $now,
            $in90Days,
            'pending_signature',
        ])->first()->toArray();
    }
}

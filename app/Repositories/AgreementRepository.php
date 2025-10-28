<?php

namespace App\Repositories;

use App\Models\Agreement;
use Illuminate\Pagination\LengthAwarePaginator;

class AgreementRepository
{
    /**
     * @return LengthAwarePaginator<int, Agreement>
     */
    public function getPaginatedAgreements(int $perPage = 15): LengthAwarePaginator
    {
        return Agreement::with('vendor')
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
}

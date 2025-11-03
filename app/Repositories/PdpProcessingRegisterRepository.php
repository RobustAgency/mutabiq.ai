<?php

namespace App\Repositories;

use App\Models\PdpProcessingRegister;
use Illuminate\Pagination\LengthAwarePaginator;

class PdpProcessingRegisterRepository
{
    /**
     * Get paginated PDP processing registers for a specific organization.
     * 
     * @param int $organizationId
     * @param int $perPage
     * @return LengthAwarePaginator<int, PdpProcessingRegister>
     */
    public function getPaginatedRegisters(int $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return PdpProcessingRegister::where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new PDP processing register.
     */
    public function createRegister(array $data): PdpProcessingRegister
    {
        return PdpProcessingRegister::create($data);
    }

    /**
     * Update an existing PDP processing register.
     */
    public function updateRegister(PdpProcessingRegister $register, array $data): PdpProcessingRegister
    {
        $register->update($data);
        return $register->fresh();
    }

    /**
     * Delete a PDP processing register.
     */
    public function deleteRegister(PdpProcessingRegister $register): bool
    {
        return $register->delete();
    }
}

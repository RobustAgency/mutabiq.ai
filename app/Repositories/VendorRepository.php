<?php

namespace App\Repositories;

use App\Models\Vendor;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorRepository
{
    /**
     * @return LengthAwarePaginator<int, Vendor>
     */
    public function getPaginatedVendors(int $organizationID, int $perPage = 15): LengthAwarePaginator
    {
        return Vendor::with('stakeholder')
            ->where('organization_id', $organizationID)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new vendor.
     */
    public function createVendor(array $data): Vendor
    {
        return Vendor::create($data);
    }

    /**
     * Update an existing vendor.
     */
    public function updateVendor(Vendor $vendor, array $data): Vendor
    {
        $vendor->update($data);
        return $vendor->fresh();
    }

    /**
     * Delete a vendor.
     */
    public function deleteVendor(Vendor $vendor): bool
    {
        return $vendor->delete();
    }
}

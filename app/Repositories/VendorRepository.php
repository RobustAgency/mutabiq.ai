<?php

namespace App\Repositories;

use App\Models\Vendor;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorRepository
{
    /**
     * @return LengthAwarePaginator<int, Vendor>
     */
    public function getFilteredVendors(array $filters = []): LengthAwarePaginator
    {
        $query = Vendor::with('stakeholder');

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }
        if (isset($filters['risk_tier'])) {
            $query->where('risk_tier', $filters['risk_tier']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['owner'])) {
            $query->whereHas('stakeholder', function ($q) use ($filters) {
                $q->where('display_name', 'like', '%'.$filters['owner'].'%');
            });
        }

        $query->when(! empty($filters['from']), function ($query) use ($filters) {
            $query->whereDate('created_at', '>=', $filters['from']);
        });

        $query->when(! empty($filters['to']), function ($query) use ($filters) {
            $query->whereDate('created_at', '<=', $filters['to']);
        });

        return $query->paginate($filters['per_page'] ?? 15);
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

    public function getStatistics(int $organizationId): array
    {
        $stats = Vendor::where('organization_id', $organizationId)
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(risk_tier IN ("tier_1", "tier_2")) as high_risk_count')
            ->selectRaw('SUM(status = "approved") as approved_count')
            ->selectRaw('SUM(status = "evaluating") as evaluating_count')
            ->first();

        $stats = $stats->toArray();

        return [
            'total_count' => (int) $stats['total_count'],
            'high_risk_count' => (int) $stats['high_risk_count'],
            'approved_count' => (int) $stats['approved_count'],
            'evaluating_count' => (int) $stats['evaluating_count'],
        ];
    }
}

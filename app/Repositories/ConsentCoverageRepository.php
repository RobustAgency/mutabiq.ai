<?php

namespace App\Repositories;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\Jurisdiction;
use App\Models\ConsentCoverage;
use App\Models\Dataset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ConsentCoverageRepository
{
    /**
     * Get paginated consent coverages for a specific organization.
     *
     * @param int $organizationId
     * @param int $perPage
     * @return LengthAwarePaginator<int, ConsentCoverage>
     */
    public function getPaginatedCoverages(int $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return ConsentCoverage::where('organization_id', $organizationId)
            ->with(['dataset', 'snapshot'])
            ->orderBy('as_of', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new consent coverage.
     *
     * @param array $data
     * @return ConsentCoverage
     */
    public function createCoverage(array $data): ConsentCoverage
    {
        if (!isset($data['created_at'])) {
            $data['created_at'] = now();
        }

        return ConsentCoverage::create($data);
    }

    /**
     * Update a consent coverage.
     *
     * @param ConsentCoverage $coverage
     * @param array $data
     * @return bool
     */
    public function updateCoverage(ConsentCoverage $coverage, array $data): bool
    {
        return $coverage->update($data);
    }

    /**
     * Delete a consent coverage.
     *
     * @param ConsentCoverage $coverage
     * @return bool
     */
    public function deleteCoverage(ConsentCoverage $coverage): bool
    {
        return $coverage->delete() ?? false;
    }
}

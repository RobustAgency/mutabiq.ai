<?php

namespace App\Repositories;

use App\Models\ConsentRecord;
use Illuminate\Pagination\LengthAwarePaginator;

class ConsentRecordRepository
{
    /**
     * Get paginated list of consent records.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, ConsentRecord>
     */
    public function getFilteredConsentRecords(array $filters = []): LengthAwarePaginator
    {
        $query = ConsentRecord::query();

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Create a new consent record.
     */
    public function createConsentRecord(array $data): ConsentRecord
    {
        return ConsentRecord::create($data);
    }

    /**
     * Update an existing consent record.
     */
    public function updateConsentRecord(ConsentRecord $consentRecord, array $data): ConsentRecord
    {
        $consentRecord->update($data);

        return $consentRecord->fresh();
    }

    /**
     * Delete a consent record.
     */
    public function deleteConsentRecord(ConsentRecord $consentRecord): bool
    {
        return $consentRecord->delete();
    }
}

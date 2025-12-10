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

        $query->when(! empty($filters['subject_realm']), function ($query) use ($filters) {
            $query->where('subject_realm', $filters['subject_realm']);
        });

        $query->when(! empty($filters['status']), function ($query) use ($filters) {
            $query->where('status', $filters['status']);
        });

        $query->when(! empty($filters['lifecycle_stage']), function ($query) use ($filters) {
            $query->where('lifecycle_stage', $filters['lifecycle_stage']);
        });

        $query->when(! empty($filters['language']), function ($query) use ($filters) {
            $query->where('language', $filters['language']);
        });

        $query->when(! empty($filters['jurisdiction']), function ($query) use ($filters) {
            $query->where('jurisdiction', $filters['jurisdiction']);
        });

        $perPage = $filters['per_page'] ?? 15;

        return $query->latest()->paginate($perPage);
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

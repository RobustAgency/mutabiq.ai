<?php

namespace App\Repositories;

use App\Models\RegulatorySubmission;
use Illuminate\Pagination\LengthAwarePaginator;

class RegulatorySubmissionRepository
{
    /**
     * Get paginated list of regulatory submissions with specified filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, RegulatorySubmission>
     */
    public function getFilteredRegulatorySubmissions(array $filters = []): LengthAwarePaginator
    {
        $query = RegulatorySubmission::with(['framework', 'aiModel', 'submittedBy']);

        $query->when(! empty($filters['authority']), function ($query) use ($filters) {
            $query->where('authority', 'like', '%'.$filters['authority'].'%');
        });

        $query->when(! empty($filters['submission_type']), function ($query) use ($filters) {
            $query->where('submission_type', $filters['submission_type']);
        });

        $query->when(! empty($filters['status']), function ($query) use ($filters) {
            $query->where('status', $filters['status']);
        });

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new regulatory submission record.
     */
    public function createRegulatorySubmission(array $data): RegulatorySubmission
    {
        return RegulatorySubmission::create($data);
    }

    /**
     * Update an existing regulatory submission record.
     */
    public function updateRegulatorySubmission(RegulatorySubmission $submission, array $data): RegulatorySubmission
    {
        $submission->update($data);

        return $submission;
    }

    /**
     * Delete a regulatory submission record.
     */
    public function deleteRegulatorySubmission(RegulatorySubmission $submission): bool
    {
        return $submission->delete();
    }
}

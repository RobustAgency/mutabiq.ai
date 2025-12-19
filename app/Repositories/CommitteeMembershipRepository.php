<?php

namespace App\Repositories;

use App\Models\CommitteeMembership;
use Illuminate\Pagination\LengthAwarePaginator;

class CommitteeMembershipRepository
{
    /**
     * Get paginated list of committee memberships with specified filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, CommitteeMembership>
     */
    public function getFilteredCommitteeMemberships(array $filters = []): LengthAwarePaginator
    {
        $query = CommitteeMembership::query();

        if (isset($filters['ai_committee_id']) && ! empty($filters['ai_committee_id'])) {
            $query->where('ai_committee_id', $filters['ai_committee_id']);
        }

        if (isset($filters['stakeholder_id']) && ! empty($filters['stakeholder_id'])) {
            $query->where('stakeholder_id', $filters['stakeholder_id']);
        }

        if (isset($filters['member_role']) && ! empty($filters['member_role'])) {
            $query->where('member_role', $filters['member_role']);
        }

        if (isset($filters['eligibility']) && ! empty($filters['eligibility'])) {
            $query->where('eligibility', $filters['eligibility']);
        }

        if (isset($filters['active'])) {
            $query->whereNull('end_date');
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new committee membership.
     */
    public function createCommitteeMembership(array $data): CommitteeMembership
    {
        return CommitteeMembership::create($data);
    }

    /**
     * Update an existing committee membership.
     */
    public function updateCommitteeMembership(CommitteeMembership $membership, array $data): CommitteeMembership
    {
        $membership->update($data);

        return $membership->fresh();
    }

    /**
     * Delete a committee membership.
     */
    public function deleteCommitteeMembership(CommitteeMembership $membership): bool
    {
        return (bool) $membership->delete();
    }
}

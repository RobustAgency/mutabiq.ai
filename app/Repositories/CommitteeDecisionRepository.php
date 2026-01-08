<?php

namespace App\Repositories;

use App\Models\CommitteeDecision;
use Illuminate\Pagination\LengthAwarePaginator;

class CommitteeDecisionRepository
{
    /**
     * @return LengthAwarePaginator<int, CommitteeDecision>
     */
    public function getFilteredCommitteeDecisions(array $filters = []): LengthAwarePaginator
    {
        $query = CommitteeDecision::with('committeeMeeting');

        if (isset($filters['committee_meeting_id'])) {
            $query->where('committee_meeting_id', $filters['committee_meeting_id']);
        }

        if (isset($filters['decision_type'])) {
            $query->where('decision_type', $filters['decision_type']);
        }

        if (isset($filters['decision_scope'])) {
            $query->where('decision_scope', $filters['decision_scope']);
        }

        if (isset($filters['vote_method'])) {
            $query->where('vote_method', $filters['vote_method']);
        }

        if (isset($filters['vote_result'])) {
            $query->where('vote_result', $filters['vote_result']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function createCommitteeDecision(array $data): CommitteeDecision
    {
        return CommitteeDecision::create($data);
    }

    public function updateCommitteeDecision(CommitteeDecision $committeeDecision, array $data): CommitteeDecision
    {
        $committeeDecision->update($data);

        return $committeeDecision->fresh()->load(['committeeMeeting', 'aiModel', 'useCase', 'control']);
    }

    public function deleteCommitteeDecision(CommitteeDecision $committeeDecision): bool
    {
        return $committeeDecision->delete();
    }
}

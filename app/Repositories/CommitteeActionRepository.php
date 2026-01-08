<?php

namespace App\Repositories;

use App\Models\CommitteeAction;
use Illuminate\Pagination\LengthAwarePaginator;

class CommitteeActionRepository
{
    /**
     * @return LengthAwarePaginator<int, CommitteeAction>
     */
    public function getFilteredCommitteeActions(array $filters = []): LengthAwarePaginator
    {
        $query = CommitteeAction::with(['committeeDecision', 'assignee']);

        if (isset($filters['committee_decision_id'])) {
            $query->where('committee_decision_id', $filters['committee_decision_id']);
        }

        if (isset($filters['action_type'])) {
            $query->where('action_type', $filters['action_type']);
        }

        if (isset($filters['assignee_id'])) {
            $query->where('assignee_id', $filters['assignee_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['verification_result'])) {
            $query->where('verification_result', $filters['verification_result']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function createCommitteeAction(array $data): CommitteeAction
    {
        return CommitteeAction::create($data);
    }

    public function updateCommitteeAction(CommitteeAction $committeeAction, array $data): CommitteeAction
    {
        $committeeAction->update($data);

        return $committeeAction->fresh()->load(['committeeDecision', 'assignee']);
    }

    public function deleteCommitteeAction(CommitteeAction $committeeAction): bool
    {
        return $committeeAction->delete();
    }
}

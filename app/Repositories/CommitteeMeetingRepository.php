<?php

namespace App\Repositories;

use App\Models\CommitteeMeeting;
use Illuminate\Pagination\LengthAwarePaginator;

class CommitteeMeetingRepository
{
    /**
     * @return LengthAwarePaginator<int, CommitteeMeeting>
     */
    public function getFilteredCommitteeMeetings(array $filters = []): LengthAwarePaginator
    {
        $query = CommitteeMeeting::with('committee');

        if (isset($filters['ai_committee_id'])) {
            $query->where('ai_committee_id', $filters['ai_committee_id']);
        }

        if (isset($filters['meeting_type'])) {
            $query->where('meeting_type', $filters['meeting_type']);
        }

        if (isset($filters['attendance_policy'])) {
            $query->where('attendance_policy', $filters['attendance_policy']);
        }

        if (isset($filters['scheduled_after'])) {
            $query->where('scheduled_at', '>=', $filters['scheduled_after']);
        }

        if (isset($filters['scheduled_before'])) {
            $query->where('scheduled_at', '<=', $filters['scheduled_before']);
        }

        return $query->orderBy('scheduled_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function createCommitteeMeeting(array $data): CommitteeMeeting
    {
        return CommitteeMeeting::create($data);
    }

    public function updateCommitteeMeeting(CommitteeMeeting $committeeMeeting, array $data): CommitteeMeeting
    {
        $committeeMeeting->update($data);

        return $committeeMeeting->fresh()->load('committee');
    }

    public function deleteCommitteeMeeting(CommitteeMeeting $committeeMeeting): bool
    {
        return $committeeMeeting->delete();
    }
}

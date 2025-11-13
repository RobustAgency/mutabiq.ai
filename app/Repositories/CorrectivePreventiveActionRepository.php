<?php

namespace App\Repositories;

use App\Models\CorrectivePreventiveAction;
use Illuminate\Pagination\LengthAwarePaginator;

class CorrectivePreventiveActionRepository
{
    /**
     * @return LengthAwarePaginator<int, CorrectivePreventiveAction>
     */
    public function getFilteredCorrectivePreventiveActions(array $filters = []): LengthAwarePaginator
    {
        $query = CorrectivePreventiveAction::with('aiModel');

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['source_type'])) {
            $query->where('source_type', $filters['source_type']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a new corrective preventive action.
     */
    public function createCorrectivePreventiveAction(array $data): CorrectivePreventiveAction
    {
        return CorrectivePreventiveAction::create($data);
    }

    /**
     * Update an existing corrective preventive action.
     */
    public function updateCorrectivePreventiveAction(CorrectivePreventiveAction $correctivePreventiveAction, array $data): CorrectivePreventiveAction
    {
        $correctivePreventiveAction->update($data);
        return $correctivePreventiveAction->fresh();
    }

    /**
     * Delete a corrective preventive action.
     */
    public function deleteCorrectivePreventiveAction(CorrectivePreventiveAction $correctivePreventiveAction): bool
    {
        return $correctivePreventiveAction->delete();
    }

    /**
     * Get a corrective preventive action by ID.
     */
    public function getCorrectivePreventiveActionById(CorrectivePreventiveAction $correctivePreventiveAction): ?CorrectivePreventiveAction
    {
        return $correctivePreventiveAction->load('aiModel');
    }
}

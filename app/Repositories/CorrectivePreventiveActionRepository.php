<?php

namespace App\Repositories;

use App\Models\CorrectivePreventiveAction;
use Illuminate\Pagination\LengthAwarePaginator;

class CorrectivePreventiveActionRepository
{
    /**
     * @return LengthAwarePaginator<int, CorrectivePreventiveAction>
     */
    public function getPaginatedCorrectivePreventiveActions(int $perPage = 15): LengthAwarePaginator
    {
        return CorrectivePreventiveAction::with('aiModel')
            ->paginate($perPage);
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

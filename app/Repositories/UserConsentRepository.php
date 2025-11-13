<?php

namespace App\Repositories;

use App\Models\UserConsent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserConsentRepository
{
    /**
     * Get paginated user consents for a specific organization.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, UserConsent>
     */
    public function getFilteredUserConsents(array $filters = []): LengthAwarePaginator
    {
        $query = UserConsent::query();

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['consent_status'])) {
            $query->where('consent_status', $filters['consent_status']);
        }

        if (isset($filters['legal_basis'])) {
            $query->where('legal_basis', $filters['legal_basis']);
        }

        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get consent by ID.
     *
     * @param int $id
     * @return UserConsent|null
     */
    public function getConsentById(int $id): ?UserConsent
    {
        return UserConsent::find($id);
    }

    /**
     * Create a new consent.
     *
     * @param array $data
     * @return UserConsent
     */
    public function createConsent(array $data): UserConsent
    {
        return UserConsent::create($data);
    }

    /**
     * Update a consent.
     *
     * @param UserConsent $consent
     * @param array $data
     * @return bool
     */
    public function updateConsent(UserConsent $consent, array $data): bool
    {
        return $consent->update($data);
    }

    /**
     * Delete a consent.
     *
     * @param UserConsent $consent
     * @return bool
     */
    public function deleteConsent(UserConsent $consent): bool
    {
        return $consent->delete() ?? false;
    }
}

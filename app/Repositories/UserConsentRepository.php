<?php

namespace App\Repositories;

use App\Enums\UserConsent\ConsentStatus;
use App\Models\UserConsent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserConsentRepository
{
    /**
     * Get paginated user consents for a specific organization.
     *
     * @param int $organizationId
     * @param int $perPage
     * @return LengthAwarePaginator<int, UserConsent>
     */
    public function getPaginatedConsents(int $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        return UserConsent::where('organization_id', $organizationId)
            ->orderBy('created_at', 'desc')
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

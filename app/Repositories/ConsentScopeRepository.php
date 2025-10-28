<?php

namespace App\Repositories;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\SubjectRealm;
use App\Models\ConsentScope;
use App\Models\Dataset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ConsentScopeRepository
{
    /**
     * Get paginated consent scopes.
     *
     * @param int $perPage
     * @return LengthAwarePaginator<int, ConsentScope>
     */
    public function getPaginatedConsentScopes(int $perPage = 15): LengthAwarePaginator
    {
        return ConsentScope::with('dataset')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new consent scope.
     *
     * @param array $data
     * @return ConsentScope
     */
    public function createConsentScope(array $data): ConsentScope
    {
        return ConsentScope::create($data);
    }

    /**
     * Update a consent scope.
     *
     * @param ConsentScope $consentScope
     * @param array $data
     * @return bool
     */
    public function updateConsentScope(ConsentScope $consentScope, array $data): bool
    {
        return $consentScope->update($data);
    }

    /**
     * Delete a consent scope.
     *
     * @param ConsentScope $consentScope
     * @return bool
     */
    public function deleteConsentScope(ConsentScope $consentScope): bool
    {
        return $consentScope->delete() ?? false;
    }
}

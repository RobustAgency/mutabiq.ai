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
     * Get paginated consent scopes for a specific organization.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, ConsentScope>
     */
    public function getFilteredConsentScopes(array $filters = []): LengthAwarePaginator
    {
        $query = ConsentScope::query();

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['subject_realm'])) {
            $query->where('subject_realm', $filters['subject_realm']);
        }

        if (isset($filters['jurisdiction'])) {
            $query->where('jurisdiction', $filters['jurisdiction']);
        }

        if (isset($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->with('dataset')
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

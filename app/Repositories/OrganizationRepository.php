<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Pagination\LengthAwarePaginator;

class OrganizationRepository
{
    /**
     * Get paginated list of organizations with specified filters.
     *
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, Organization>
     */
    public function getFilteredOrganizations(array $filters = []): LengthAwarePaginator
    {
        $query = Organization::query();

        if (! empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (! empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        if (array_key_exists('is_active', $filters)) {
            $query->where('is_active', $filters['is_active']);
        }

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new organization for the given user.
     */
    public function createForUser(User $user, array $organizationData): Organization
    {
        $organizationData['user_id'] = $user->id;

        $organization = Organization::create($organizationData);
        $user->update(['organization_id' => $organization->id]);

        return $organization;
    }

    /**
     * Update the given organization with new data.
     */
    public function update(Organization $organization, array $organizationData): Organization
    {
        $organization->update($organizationData);

        return $organization;
    }

    /**
     * Get an organization with its members by user ID.
     */
    public function getOrganizationWithMembersByUserID(int $userID): Organization
    {
        return Organization::with('members')->where('user_id', $userID)->first();
    }
}

<?php

namespace App\Repositories;

use App\Models\PrivacyIncident;
use Illuminate\Pagination\LengthAwarePaginator;

class PrivacyIncidentRepository
{
    /**
     * Get paginated list of privacy incidents with specified filters.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, PrivacyIncident>
     */
    public function getFilteredPrivacyIncidents(array $filters = []): LengthAwarePaginator
    {
        $query = PrivacyIncident::query();

        if (isset($filters['incident_type']) && ! empty($filters['incident_type'])) {
            $query->where('incident_type', $filters['incident_type']);
        }

        if (isset($filters['risk_level']) && ! empty($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }

        if (isset($filters['status']) && ! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_breach'])) {
            $query->where('is_breach', (bool) $filters['is_breach']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function createPrivacyIncident(array $data): PrivacyIncident
    {
        return PrivacyIncident::create($data);
    }

    public function updatePrivacyIncident(PrivacyIncident $privacyIncident, array $data): PrivacyIncident
    {
        $privacyIncident->update($data);

        return $privacyIncident->fresh();
    }

    public function deletePrivacyIncident(PrivacyIncident $privacyIncident): bool
    {
        return (bool) $privacyIncident->delete();
    }
}

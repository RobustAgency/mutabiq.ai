<?php

namespace App\Repositories;

use App\Models\DataSource;
use Illuminate\Pagination\LengthAwarePaginator;

class DataSourceRepository
{
    /**
     * Get paginated data sources for a specific organization.
     *
     * @param  array<string, mixed>  $filter
     * @return LengthAwarePaginator<int , DataSource>
     */
    public function getFilteredDataSources(array $filter = []): LengthAwarePaginator
    {
        $query = DataSource::query();

        if (isset($filter['organization_id'])) {
            $query->where('organization_id', $filter['organization_id']);
        }

        if (isset($filter['name'])) {
            $query->where('name', 'like', '%'.$filter['name'].'%');
        }

        if (isset($filter['system_type'])) {
            $query->where('system_type', $filter['system_type']);
        }

        if (isset($filter['criticality_level'])) {
            $query->where('criticality_level', $filter['criticality_level']);
        }

        if (isset($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        if (isset($filter['from'])) {
            $query->whereDate('created_at', '>=', $filter['from']);
        }

        if (isset($filter['to'])) {
            $query->whereDate('created_at', '<=', $filter['to']);
        }

        return $query->paginate($filter['per_page'] ?? 15);
    }

    /**
     * Get data source by ID.
     */
    public function getDataSourceById(int $id): ?DataSource
    {
        return DataSource::find($id);
    }

    /**
     * Create a new data source.
     */
    public function createDataSource(array $data): DataSource
    {
        return DataSource::create($data);
    }

    /**
     * Update a data source.
     */
    public function updateDataSource(DataSource $dataSource, array $data): bool
    {
        return $dataSource->update($data);
    }

    /**
     * Delete a data source.
     */
    public function delete(DataSource $dataSource): bool
    {
        return $dataSource->delete();
    }
}

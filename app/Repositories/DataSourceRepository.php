<?php

namespace App\Repositories;

use App\Models\DataSource;
use Illuminate\Pagination\LengthAwarePaginator;

class DataSourceRepository
{
    /**
     * Get paginated data sources for a specific organization.
     *
     * @param array<string, mixed> $filter
     * @return LengthAwarePaginator<int , DataSource>
     */
    public function getFilteredDataSources(array $filter = []): LengthAwarePaginator
    {
        $query = DataSource::query();

        if (isset($filter['organization_id'])) {
            $query->where('organization_id', $filter['organization_id']);
        }

        if (isset($filter['name'])) {
            $query->where('name', 'like', '%' . $filter['name'] . '%');
        }

        if (isset($filter['system_type'])) {
            $query->where('system_type', $filter['system_type']);
        }

        if (isset($filter['access_method'])) {
            $query->where('access_method', $filter['access_method']);
        }

        if (isset($filter['classification'])) {
            $query->where('classification', $filter['classification']);
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
     *
     * @param int $id
     * @return DataSource|null
     */
    public function getDataSourceById(int $id): ?DataSource
    {
        return DataSource::find($id);
    }

    /**
     * Create a new data source.
     *
     * @param array $data
     * @return DataSource
     */
    public function createDataSource(array $data): DataSource
    {
        return DataSource::create($data);
    }

    /**
     * Update a data source.
     *
     * @param DataSource $dataSource
     * @param array $data
     * @return bool
     */
    public function updateDataSource(DataSource $dataSource, array $data): bool
    {
        return $dataSource->update($data);
    }

    /**
     * Delete a data source.
     *
     * @param DataSource $dataSource
     * @return bool
     */
    public function delete(DataSource $dataSource): bool
    {
        return $dataSource->delete();
    }
}

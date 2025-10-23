<?php

namespace App\Repositories;

use App\Models\DataSource;
use Illuminate\Pagination\LengthAwarePaginator;

class DataSourceRepository
{
    /**
     * Get paginated data sources.
     *
     * @param int $perPage
     * @return LengthAwarePaginator<int , DataSource>
     */
    public function getPaginatedDataSources(int $perPage = 15): LengthAwarePaginator
    {
        return DataSource::paginate($perPage);
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

<?php

namespace App\Repositories;

use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DatasetSnapshotRepository
{
    /**
     * Get paginated dataset snapshots for a specific organization.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, DatasetSnapshot>
     */
    public function getFilteredDatasetSnapshots(array $filters = []): LengthAwarePaginator
    {
        $query = DatasetSnapshot::query();

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query->with('dataset')->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get snapshots for a specific dataset.
     *
     * @param Dataset|int $dataset
     * @return Collection<int, DatasetSnapshot>
     */
    public function getSnapshotsForDataset(Dataset|int $dataset): Collection
    {
        $datasetId = $dataset instanceof Dataset ? $dataset->id : $dataset;

        return DatasetSnapshot::where('dataset_id', $datasetId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get snapshot by ID.
     *
     * @param int $id
     * @return DatasetSnapshot|null
     */
    public function getSnapshotById(int $id): ?DatasetSnapshot
    {
        return DatasetSnapshot::with('dataset')->find($id);
    }

    /**
     * Create a new snapshot.
     *
     * @param array $data
     * @return DatasetSnapshot
     */
    public function createSnapshot(array $data): DatasetSnapshot
    {
        return DatasetSnapshot::create($data);
    }

    public function updateSnapshot(DatasetSnapshot $snapshot, array $data): bool
    {
        return $snapshot->update($data);
    }

    /**
     * Delete a snapshot.
     *
     * @param DatasetSnapshot $snapshot
     * @return bool
     */
    public function deleteSnapshot(DatasetSnapshot $snapshot): bool
    {
        return $snapshot->delete() ?? false;
    }
}

<?php

namespace App\Repositories;

use App\Models\DataElement;
use App\Models\DatasetDataElement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class DataElementRepository
{
    /**
     * Get paginated data elements for a specific organization.
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, DataElement>
     */
    public function getFilteredDataElements(array $filters = []): LengthAwarePaginator
    {
        $query = DataElement::query()->with('datasets');

        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['data_type'])) {
            $query->where('data_type', $filters['data_type']);
        }

        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function createDataElement(array $data): DataElement
    {
        return DataElement::create($data);
    }

    public function getDataElementByID(int $id): ?DataElement
    {
        $query = DataElement::with('datasets');
        return $query->find($id);
    }

    public function updateDataElement(DataElement $dataElement, array $data): DataElement
    {
        $dataElement->update($data);

        return $dataElement->fresh();
    }

    public function delete(DataElement $dataElement): bool
    {
        return $dataElement->delete() ?? false;
    }

    public function associateDataElementWithDataset(array $data): DatasetDataElement
    {
        $data['organization_id'] = Auth::user()->organization_id;
        return DatasetDataElement::create($data);
    }
}

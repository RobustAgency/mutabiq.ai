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
     * @param int $organizationId
     * @param int $perPage
     * @return LengthAwarePaginator<int, DataElement>
     */
    public function getPaginatedDataElements(int $organizationId, int $perPage = 15): LengthAwarePaginator
    {
        $query = DataElement::where('organization_id', $organizationId)->with('datasets');
        return $query->paginate($perPage);
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

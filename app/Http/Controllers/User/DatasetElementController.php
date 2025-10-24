<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\DatasetElementMap\StoreDatasetElementMapRequest;
use App\Repositories\DataElementRepository;
use Illuminate\Http\JsonResponse;

class DatasetElementController extends Controller
{
    public function __construct(private DataElementRepository $dataElementRepository) {}

    public function store(StoreDatasetElementMapRequest $request): JsonResponse
    {
        $data = $request->validated();
        $datasetElement = $this->dataElementRepository->associateDataElementWithDataset($data);

        return response()->json([
            'error' => false,
            'message' => 'Data Element associated with Dataset successfully.',
            'data' => $datasetElement,
        ], 201);
    }
}

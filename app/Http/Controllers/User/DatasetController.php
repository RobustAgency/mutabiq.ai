<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dataset\ListDatasetRequest;
use App\Http\Requests\Dataset\StoreDatasetRequest;
use App\Http\Requests\Dataset\UpdateDatasetRequest;
use App\Http\Resources\DatasetResource;
use App\Models\Dataset;
use App\Repositories\DatasetRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DatasetController extends Controller
{
    public function __construct(private DatasetRepository $datasetRepository) {}

    /**
     * Display a listing of datasets.
     */
    public function index(ListDatasetRequest $request): JsonResponse
    {
        $organizationId = Auth::user()->organization_id;
        $perPage = $request->input('per_page', 15);
        $datasets = $this->datasetRepository->getPaginatedDatasets($organizationId, $perPage);

        return response()->json([
            'error' => false,
            'data' => $datasets,
            'message' => 'Datasets retrieved successfully'
        ]);
    }

    /**
     * Store a newly created dataset.
     */
    public function store(StoreDatasetRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = $request->user()->organization_id;

        $dataset = $this->datasetRepository->createDataset($validated);

        return response()->json([
            'error' => false,
            'message' => 'Dataset created successfully',
            'data' => $dataset
        ], 201);
    }

    /**
     * Display the specified dataset.
     */
    public function show(Dataset $dataset): JsonResponse
    {
        return response()->json([
            'error' => false,
            'data' => new DatasetResource($dataset),
            'message' => 'Dataset retrieved successfully'
        ]);
    }

    /**
     * Update the specified dataset.
     */
    public function update(UpdateDatasetRequest $request, Dataset $dataset): JsonResponse
    {
        $validated = $request->validated();

        $this->datasetRepository->updateDataset($dataset, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Dataset updated successfully',
            'data' => $dataset->fresh()
        ], 200);
    }

    /**
     * Remove the specified dataset.
     */
    public function destroy(Dataset $dataset): JsonResponse
    {
        $this->datasetRepository->delete($dataset);

        return response()->json([
            'error' => false,
            'message' => 'Dataset deleted successfully',
            'data' => null,
        ]);
    }
}

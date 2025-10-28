<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\DatasetSnapshot\ListDatasetSnapshotRequest;
use App\Http\Requests\DatasetSnapshot\StoreDatasetSnapshotRequest;
use App\Http\Requests\DatasetSnapshot\UpdateDatasetSnapshotRequest;
use App\Http\Resources\DatasetSnapshotResource;
use App\Models\DatasetSnapshot;
use App\Repositories\DatasetSnapshotRepository;
use Illuminate\Http\JsonResponse;

class DatasetSnapshotController extends Controller
{
    public function __construct(private DatasetSnapshotRepository $datasetSnapshotRepository) {}

    /**
     * Display a listing of dataset snapshots.
     */
    public function index(ListDatasetSnapshotRequest $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $snapshots = $this->datasetSnapshotRepository->getPaginatedSnapshots($perPage);

        return response()->json([
            'error' => false,
            'data' => $snapshots,
            'message' => 'Dataset snapshots retrieved successfully'
        ]);
    }

    /**
     * Store a newly created dataset snapshot.
     */
    public function store(StoreDatasetSnapshotRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $snapshot = $this->datasetSnapshotRepository->createSnapshot($validated);

        return response()->json([
            'error' => false,
            'message' => 'Dataset snapshot created successfully',
            'data' => new DatasetSnapshotResource($snapshot)
        ], 201);
    }

    /**
     * Display the specified dataset snapshot.
     */
    public function show(DatasetSnapshot $datasetSnapshot): JsonResponse
    {
        $snapshot = $this->datasetSnapshotRepository->getSnapshotById($datasetSnapshot->id);

        return response()->json([
            'error' => false,
            'data' => new DatasetSnapshotResource($snapshot),
            'message' => 'Dataset snapshot retrieved successfully'
        ]);
    }

    /**
     * Update the specified dataset snapshot.
     */
    public function update(UpdateDatasetSnapshotRequest $request, DatasetSnapshot $datasetSnapshot): JsonResponse
    {
        $validated = $request->validated();

        $this->datasetSnapshotRepository->updateSnapshot($datasetSnapshot, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Dataset snapshot updated successfully',
            'data' => new DatasetSnapshotResource($datasetSnapshot->fresh())
        ], 200);
    }

    /**
     * Remove the specified dataset snapshot.
     */
    public function destroy(DatasetSnapshot $datasetSnapshot): JsonResponse
    {
        $this->datasetSnapshotRepository->deleteSnapshot($datasetSnapshot);

        return response()->json([
            'error' => false,
            'message' => 'Dataset snapshot deleted successfully',
            'data' => null,
        ]);
    }
}

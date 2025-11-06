<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListArtifactAccessLogRequest;
use App\Http\Requests\StoreArtifactAccessLogRequest;
use App\Http\Resources\ArtifactAccessLogResource;
use App\Models\ArtifactAccessLog;
use App\Repositories\ArtifactAccessLogRepository;
use Illuminate\Http\JsonResponse;

class ArtifactAccessLogController extends Controller
{
    protected ArtifactAccessLogRepository $repository;

    public function __construct(ArtifactAccessLogRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of artifact access logs.
     */
    public function index(ListArtifactAccessLogRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $logs = $this->repository->getFilteredArtifactAccessLogs($validated);

        return response()->json([
            'error' => false,
            'data' => $logs,
            'message' => 'Artifact access logs retrieved successfully'
        ]);
    }

    /**
     * Store a newly created artifact access log.
     */
    public function store(StoreArtifactAccessLogRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $log = $this->repository->createArtifactAccessLog($validated);

        return response()->json([
            'error' => false,
            'data' => new ArtifactAccessLogResource($log),
            'message' => 'Artifact access log created successfully'
        ]);
    }

    /**
     * Display the specified artifact access log.
     */
    public function show(ArtifactAccessLog $log): JsonResponse
    {
        return response()->json([
            'error' => false,
            'data' => new ArtifactAccessLogResource($log),
            'message' => 'Artifact access log retrieved successfully'
        ]);
    }

    /**
     * Remove the specified artifact access log from storage.
     */
    public function destroy(ArtifactAccessLog $log): JsonResponse
    {
        $log->delete();

        return response()->json([
            'error' => false,
            'message' => 'Artifact access log deleted successfully'
        ]);
    }
}

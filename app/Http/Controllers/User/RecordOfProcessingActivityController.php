<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\RecordOfProcessingActivity;
use App\Http\Resources\RecordOfProcessingActivityResource;
use App\Repositories\RecordOfProcessingActivityRepository;
use App\Http\Requests\RecordOfProcessingActivity\ListRecordOfProcessingActivityRequest;
use App\Http\Requests\RecordOfProcessingActivity\StoreRecordOfProcessingActivityRequest;
use App\Http\Requests\RecordOfProcessingActivity\UpdateRecordOfProcessingActivityRequest;

class RecordOfProcessingActivityController extends Controller
{
    public function __construct(
        private readonly RecordOfProcessingActivityRepository $repository
    ) {}

    /**
     * Display a paginated listing of processing activities.
     */
    public function index(ListRecordOfProcessingActivityRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $activities = $this->repository->getFilteredActivities($filters);

        return response()->json([
            'error' => false,
            'message' => 'Processing activities retrieved successfully',
            'data' => $activities,
        ]);
    }

    /**
     * Store a newly created processing activity.
     */
    public function store(StoreRecordOfProcessingActivityRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $uuid = Str::uuid()->toString();
        $validated['activity_code'] = 'PROC-'.$uuid;
        $validated['created_by'] = Auth::user()->id;
        $validated['updated_by'] = Auth::user()->id;

        $activity = $this->repository->createActivity($validated);

        return response()->json([
            'error' => false,
            'message' => 'Processing activity created successfully',
            'data' => new RecordOfProcessingActivityResource($activity),
        ], 201);
    }

    /**
     * Display the specified processing activity.
     */
    public function show(RecordOfProcessingActivity $recordOfProcessingActivity): JsonResponse
    {
        return response()->json([
            'error' => false,
            'message' => 'Processing activity retrieved successfully',
            'data' => new RecordOfProcessingActivityResource($recordOfProcessingActivity),
        ]);
    }

    /**
     * Update the specified processing activity.
     */
    public function update(
        UpdateRecordOfProcessingActivityRequest $request,
        RecordOfProcessingActivity $recordOfProcessingActivity
    ): JsonResponse {
        $validated = $request->validated();
        $validated['updated_by'] = Auth::user()->id;

        $activity = $this->repository->updateActivity($recordOfProcessingActivity, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Processing activity updated successfully',
            'data' => new RecordOfProcessingActivityResource($activity),
        ]);
    }

    /**
     * Remove the specified processing activity.
     */
    public function destroy(RecordOfProcessingActivity $recordOfProcessingActivity): JsonResponse
    {
        $this->repository->deleteActivity($recordOfProcessingActivity);

        return response()->json([
            'error' => false,
            'message' => 'Processing activity deleted successfully',
            'data' => null,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ActivityLogRepository;
use App\Http\Requests\ActivityLog\ListActivityLogRequest;

class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogRepository $activityLogRepository
    ) {}

    /**
     * Display a listing of activity logs.
     */
    public function index(ListActivityLogRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();
        $validated['organization_id'] = $user->organization_id;
        $activityLogs = $this->activityLogRepository->getFilteredActivityLog($validated);

        return response()->json([
            'error' => false,
            'message' => 'Activity logs retrieved successfully',
            'data' => $activityLogs,
        ]);
    }
}

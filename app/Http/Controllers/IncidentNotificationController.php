<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncidentNotification\ListIncidentNotificationRequest;
use App\Http\Requests\IncidentNotification\StoreIncidentNotificationRequest;
use App\Http\Requests\IncidentNotification\UpdateIncidentNotificationRequest;
use App\Http\Resources\IncidentNotificationResource;
use App\Models\IncidentNotification;
use App\Repositories\IncidentNotificationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidentNotificationController extends Controller
{
    public function __construct(
        protected IncidentNotificationRepository $incidentNotificationRepository
    ) {}

    /**
     * Display a listing of incident notifications.
     */
    public function index(ListIncidentNotificationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = Auth::user()->organization_id;
        $incidentNotifications = $this->incidentNotificationRepository->getFilteredIncidentNotifications($validated);
        return response()->json([
            'error' => false,
            'message' => 'Incident notifications retrieved successfully',
            'data' => $incidentNotifications,
        ]);
    }

    /**
     * Store a newly created incident notification.
     */
    public function store(StoreIncidentNotificationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = Auth::user()->organization_id;
        $incidentNotification = $this->incidentNotificationRepository->createIncidentNotification($validated);

        return response()->json([
            'error' => false,
            'message' => 'Incident notification created successfully',
            'data' => new IncidentNotificationResource($incidentNotification),
        ], 201);
    }

    /**
     * Display the specified incident notification.
     */
    public function show(IncidentNotification $incidentNotification): JsonResponse
    {
        $incidentNotification = $this->incidentNotificationRepository->getIncidentNotificationById($incidentNotification);
        return response()->json([
            'error' => false,
            'message' => 'Incident notification retrieved successfully',
            'data' => new IncidentNotificationResource($incidentNotification),
        ]);
    }

    /**
     * Update the specified incident notification.
     */
    public function update(UpdateIncidentNotificationRequest $request, IncidentNotification $incidentNotification): JsonResponse
    {
        $updatedIncidentNotification = $this->incidentNotificationRepository->updateIncidentNotification(
            $incidentNotification,
            $request->validated()
        );

        return response()->json([
            'error' => false,
            'message' => 'Incident notification updated successfully',
            'data' => new IncidentNotificationResource($updatedIncidentNotification),
        ]);
    }

    /**
     * Remove the specified incident notification.
     */
    public function destroy(IncidentNotification $incidentNotification): JsonResponse
    {
        $this->incidentNotificationRepository->deleteIncidentNotification($incidentNotification);

        return response()->json([
            'error' => false,
            'message' => 'Incident notification deleted successfully',
        ]);
    }
}

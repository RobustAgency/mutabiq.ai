<?php

namespace App\Http\Controllers;

use App\Models\IncidentAlert;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\IncidentAlertResource;
use App\Repositories\IncidentAlertRepository;
use App\Http\Requests\IncidentAlert\ListIncidentAlertRequest;
use App\Http\Requests\IncidentAlert\StoreIncidentAlertRequest;
use App\Http\Requests\IncidentAlert\UpdateIncidentAlertRequest;

class IncidentAlertController extends Controller
{
    public function __construct(
        protected IncidentAlertRepository $incidentAlertRepository
    ) {}

    /**
     * Display a listing of incident alerts.
     */
    public function index(ListIncidentAlertRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = $request->user()->organization_id;
        $incidentAlerts = $this->incidentAlertRepository->getFilteredIncidentAlerts($validated);

        return response()->json([
            'error' => false,
            'message' => 'Incident alerts retrieved successfully',
            'data' => $incidentAlerts,
        ]);
    }

    /**
     * Store a newly created incident alert.
     */
    public function store(StoreIncidentAlertRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = $request->user()->organization_id;
        $incidentAlert = $this->incidentAlertRepository->createIncidentAlert($validated);

        return response()->json([
            'error' => false,
            'message' => 'Incident alert created successfully',
            'data' => new IncidentAlertResource($incidentAlert),
        ], 201);
    }

    /**
     * Display the specified incident alert.
     */
    public function show(IncidentAlert $incidentAlert): JsonResponse
    {
        $incidentAlert = $this->incidentAlertRepository->getIncidentAlertById($incidentAlert);

        return response()->json([
            'error' => false,
            'message' => 'Incident alert retrieved successfully',
            'data' => new IncidentAlertResource($incidentAlert),
        ]);
    }

    /**
     * Update the specified incident alert.
     */
    public function update(UpdateIncidentAlertRequest $request, IncidentAlert $incidentAlert): JsonResponse
    {
        $updatedIncidentAlert = $this->incidentAlertRepository->updateIncidentAlert(
            $incidentAlert,
            $request->validated()
        );

        return response()->json([
            'error' => false,
            'message' => 'Incident alert updated successfully',
            'data' => new IncidentAlertResource($updatedIncidentAlert),
        ]);
    }

    /**
     * Remove the specified incident alert.
     */
    public function destroy(IncidentAlert $incidentAlert): JsonResponse
    {
        $this->incidentAlertRepository->deleteIncidentAlert($incidentAlert);

        return response()->json([
            'error' => false,
            'message' => 'Incident alert deleted successfully',
        ]);
    }
}

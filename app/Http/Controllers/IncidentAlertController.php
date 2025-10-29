<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncidentAlert\StoreIncidentAlertRequest;
use App\Http\Requests\IncidentAlert\UpdateIncidentAlertRequest;
use App\Http\Resources\IncidentAlertResource;
use App\Models\IncidentAlert;
use App\Repositories\IncidentAlertRepository;
use Illuminate\Http\JsonResponse;

class IncidentAlertController extends Controller
{
    public function __construct(
        protected IncidentAlertRepository $incidentAlertRepository
    ) {}

    /**
     * Display a listing of incident alerts.
     */
    public function index(): JsonResponse
    {
        $incidentAlerts = $this->incidentAlertRepository->getPaginatedIncidentAlerts();

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
        $incidentAlert = $this->incidentAlertRepository->createIncidentAlert($request->validated());

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

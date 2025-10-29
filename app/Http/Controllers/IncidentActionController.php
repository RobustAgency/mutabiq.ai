<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncidentAction\StoreIncidentActionRequest;
use App\Http\Requests\IncidentAction\UpdateIncidentActionRequest;
use App\Http\Resources\IncidentActionResource;
use App\Models\IncidentAction;
use App\Repositories\IncidentActionRepository;
use Illuminate\Http\JsonResponse;

class IncidentActionController extends Controller
{
    public function __construct(
        protected IncidentActionRepository $incidentActionRepository
    ) {}

    /**
     * Display a listing of incident actions.
     */
    public function index(): JsonResponse
    {
        $incidentActions = $this->incidentActionRepository->getPaginatedIncidentActions();

        return response()->json([
            'error' => false,
            'message' => 'Incident actions retrieved successfully',
            'data' => $incidentActions,
        ]);
    }

    /**
     * Store a newly created incident action.
     */
    public function store(StoreIncidentActionRequest $request): JsonResponse
    {
        $incidentAction = $this->incidentActionRepository->createIncidentAction($request->validated());

        return response()->json([
            'error' => false,
            'message' => 'Incident action created successfully',
            'data' => new IncidentActionResource($incidentAction),
        ], 201);
    }

    /**
     * Display the specified incident action.
     */
    public function show(IncidentAction $incidentAction): JsonResponse
    {
        $incidentAction = $this->incidentActionRepository->getIncidentActionById($incidentAction);
        return response()->json([
            'error' => false,
            'message' => 'Incident action retrieved successfully',
            'data' => new IncidentActionResource($incidentAction),
        ]);
    }

    /**
     * Update the specified incident action.
     */
    public function update(UpdateIncidentActionRequest $request, IncidentAction $incidentAction): JsonResponse
    {
        $updatedIncidentAction = $this->incidentActionRepository->updateIncidentAction(
            $incidentAction,
            $request->validated()
        );

        return response()->json([
            'error' => false,
            'message' => 'Incident action updated successfully',
            'data' => new IncidentActionResource($updatedIncidentAction),
        ]);
    }

    /**
     * Remove the specified incident action.
     */
    public function destroy(IncidentAction $incidentAction): JsonResponse
    {
        $this->incidentActionRepository->deleteIncidentAction($incidentAction);

        return response()->json([
            'error' => false,
            'message' => 'Incident action deleted successfully',
        ]);
    }
}

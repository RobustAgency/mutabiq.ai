<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiIncident\StoreAiIncidentRequest;
use App\Http\Requests\AiIncident\UpdateAiIncidentRequest;
use App\Http\Resources\AiIncidentResource;
use App\Models\AiIncident;
use App\Repositories\AiIncidentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiIncidentController extends Controller
{
    public function __construct(
        private readonly AiIncidentRepository $repository
    ) {}

    /**
     * Display a paginated listing of AI incidents.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $aiIncidents = $this->repository->getPaginatedAiIncidents($perPage);

        return response()->json([
            'error' => false,
            'message' => 'AI incidents retrieved successfully',
            'data' => $aiIncidents,
        ]);
    }

    /**
     * Store a newly created AI incident.
     */
    public function store(StoreAiIncidentRequest $request): JsonResponse
    {
        $aiIncident = $this->repository->createAiIncident($request->validated());

        return response()->json([
            'error' => false,
            'message' => 'AI incident created successfully',
            'data' => new AiIncidentResource($aiIncident),
        ], 201);
    }

    /**
     * Display the specified AI incident.
     */
    public function show(AiIncident $aiIncident): JsonResponse
    {
        return response()->json([
            'error' => false,
            'message' => 'AI incident retrieved successfully',
            'data' => new AiIncidentResource($aiIncident),
        ]);
    }

    /**
     * Update the specified AI incident.
     */
    public function update(UpdateAiIncidentRequest $request, AiIncident $aiIncident): JsonResponse
    {
        $updated = $this->repository->updateAiIncident($aiIncident, $request->validated());

        return response()->json([
            'error' => false,
            'message' => 'AI incident updated successfully',
            'data' => new AiIncidentResource($updated),
        ]);
    }

    /**
     * Remove the specified AI incident.
     */
    public function destroy(AiIncident $aiIncident): JsonResponse
    {
        $this->repository->deleteAiIncident($aiIncident);

        return response()->json([
            'error' => false,
            'message' => 'AI incident deleted successfully',
        ]);
    }
}

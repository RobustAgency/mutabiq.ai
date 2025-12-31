<?php

namespace App\Http\Controllers\User;

use App\Models\AiIncident;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AiIncidentResource;
use App\Repositories\AiIncidentRepository;
use App\Http\Requests\AiIncident\ListAiIncidentRequest;
use App\Http\Requests\AiIncident\StoreAiIncidentRequest;
use App\Http\Requests\AiIncident\UpdateAiIncidentRequest;

class AiIncidentController extends Controller
{
    public function __construct(
        private readonly AiIncidentRepository $repository
    ) {}

    /**
     * Display a paginated listing of AI incidents.
     */
    public function index(ListAiIncidentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = Auth::user()->organization_id;
        $aiIncidents = $this->repository->getFilteredAiIncident($validated);

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
        $validated = $request->validated();
        $validated['organization_id'] = Auth::user()->organization_id;
        $aiIncident = $this->repository->createAiIncident($validated);

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

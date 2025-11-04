<?php

namespace App\Http\Controllers;

use App\Http\Requests\CorrectivePreventiveAction\StoreCorrectivePreventiveActionRequest;
use App\Http\Requests\CorrectivePreventiveAction\UpdateCorrectivePreventiveActionRequest;
use App\Http\Resources\CorrectivePreventiveActionResource;
use App\Models\CorrectivePreventiveAction;
use App\Repositories\CorrectivePreventiveActionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CorrectivePreventiveActionController extends Controller
{
    public function __construct(
        protected CorrectivePreventiveActionRepository $repository
    ) {}

    /**
     * Display a listing of corrective preventive actions.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page') ?? 15;
        $organizationID = $request->user()->organization_id;
        $correctivePreventiveActions = $this->repository->getPaginatedCorrectivePreventiveActions($organizationID, $perPage);

        return response()->json([
            'error' => false,
            'message' => 'Corrective preventive actions retrieved successfully',
            'data' => $correctivePreventiveActions,
        ]);
    }

    /**
     * Store a newly created corrective preventive action.
     */
    public function store(StoreCorrectivePreventiveActionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = $request->user()->organization_id;
        $correctivePreventiveAction = $this->repository->createCorrectivePreventiveAction($validated);

        return response()->json([
            'error' => false,
            'message' => 'Corrective preventive action created successfully',
            'data' => new CorrectivePreventiveActionResource($correctivePreventiveAction),
        ], 201);
    }

    /**
     * Display the specified corrective preventive action.
     */
    public function show(CorrectivePreventiveAction $correctivePreventiveAction): JsonResponse
    {
        $correctivePreventiveAction = $this->repository->getCorrectivePreventiveActionById($correctivePreventiveAction);

        return response()->json([
            'error' => false,
            'message' => 'Corrective preventive action retrieved successfully',
            'data' => new CorrectivePreventiveActionResource($correctivePreventiveAction),
        ]);
    }

    /**
     * Update the specified corrective preventive action.
     */
    public function update(UpdateCorrectivePreventiveActionRequest $request, CorrectivePreventiveAction $correctivePreventiveAction): JsonResponse
    {
        $correctivePreventiveAction = $this->repository->updateCorrectivePreventiveAction(
            $correctivePreventiveAction,
            $request->validated()
        );

        return response()->json([
            'error' => false,
            'message' => 'Corrective preventive action updated successfully',
            'data' => new CorrectivePreventiveActionResource($correctivePreventiveAction),
        ]);
    }

    /**
     * Remove the specified corrective preventive action.
     */
    public function destroy(CorrectivePreventiveAction $correctivePreventiveAction): JsonResponse
    {
        $this->repository->deleteCorrectivePreventiveAction($correctivePreventiveAction);

        return response()->json([
            'error' => false,
            'message' => 'Corrective preventive action deleted successfully',
            'data' => null,
        ]);
    }
}

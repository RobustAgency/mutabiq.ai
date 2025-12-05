<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use App\Models\RequirementControl;
use App\Http\Controllers\Controller;
use App\Http\Resources\RequirementControlResource;
use App\Repositories\RequirementControlRepository;
use App\Http\Requests\RequirementControl\ListRequirementControlRequest;
use App\Http\Requests\RequirementControl\StoreRequirementControlRequest;
use App\Http\Requests\RequirementControl\UpdateRequirementControlRequest;

class RequirementControlController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private RequirementControlRepository $requirementControlRepository
    ) {}

    /**
     * Display a paginated listing of requirement controls.
     */
    public function index(ListRequirementControlRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $requirementControls = $this->requirementControlRepository->getFilteredRequirementControls($validated);

        return response()->json([
            'error' => false,
            'message' => 'Requirement controls retrieved successfully',
            'data' => $requirementControls,
        ]);
    }

    /**
     * Store a newly created requirement control mapping.
     */
    public function store(StoreRequirementControlRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $requirementControl = $this->requirementControlRepository->createRequirementControl($validated);

        return response()->json([
            'error' => false,
            'message' => 'Requirement control created successfully',
            'data' => $requirementControl,
        ], 201);
    }

    /**
     * Display the specified requirement control.
     */
    public function show(RequirementControl $requirementControl): JsonResponse
    {
        $requirementControl->load(['requirement', 'control', 'user']);

        return response()->json([
            'error' => false,
            'message' => 'Requirement control retrieved successfully',
            'data' => new RequirementControlResource($requirementControl),
        ]);
    }

    /**
     * Update the specified requirement control.
     */
    public function update(UpdateRequirementControlRequest $request, RequirementControl $requirementControl): JsonResponse
    {
        $validated = $request->validated();
        $requirementControl = $this->requirementControlRepository->updateRequirementControl($requirementControl, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Requirement control updated successfully',
            'data' => $requirementControl,
        ]);
    }

    /**
     * Remove the specified requirement control.
     */
    public function destroy(RequirementControl $requirementControl): JsonResponse
    {
        $this->requirementControlRepository->deleteRequirementControl($requirementControl);

        return response()->json([
            'error' => false,
            'message' => 'Requirement control deleted successfully',
            'data' => null,
        ]);
    }
}

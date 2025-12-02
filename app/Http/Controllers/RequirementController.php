<?php

namespace App\Http\Controllers;

use App\Models\Requirement;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RequirementResource;
use App\Repositories\RequirementRepository;
use App\Http\Requests\StoreRequirementRequest;
use App\Http\Requests\SearchRequirementRequest;
use App\Http\Requests\UpdateRequirementRequest;

class RequirementController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private RequirementRepository $requirementRepository
    ) {}

    public function index(SearchRequirementRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $requirements = $this->requirementRepository->getFilteredRequirements($validated);

        return response()->json([
            'error' => false,
            'message' => 'Requirements retrieved successfully',
            'data' => $requirements,
        ]);
    }

    public function store(StoreRequirementRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $this->requirementRepository->createForAdmin($validated);

        return response()->json([
            'error' => false,
            'message' => 'Requirement created successfully',
            'data' => null,
        ], 201);
    }

    public function show(Requirement $requirement): JsonResponse
    {
        $requirement->load('framework');

        return response()->json([
            'error' => false,
            'message' => 'Requirement retrieved successfully',
            'data' => new RequirementResource($requirement),
        ]);
    }

    public function update(UpdateRequirementRequest $request, Requirement $requirement): JsonResponse
    {
        $validated = $request->validated();

        $requirement = $this->requirementRepository->update($requirement, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Requirement updated successfully',
            'data' => null,
        ]);
    }
}

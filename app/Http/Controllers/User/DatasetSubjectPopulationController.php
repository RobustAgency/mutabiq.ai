<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\DatasetSubjectPopulation\StoreDatasetSubjectPopulationRequest;
use App\Http\Requests\DatasetSubjectPopulation\UpdateDatasetSubjectPopulationRequest;
use App\Http\Resources\DatasetSubjectPopulationResource;
use App\Models\DatasetSubjectPopulation;
use App\Repositories\DatasetSubjectPopulationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DatasetSubjectPopulationController extends Controller
{
    public function __construct(
        private readonly DatasetSubjectPopulationRepository $repository
    ) {}

    /**
     * Display a paginated listing of dataset subject populations.
     */
    public function index(Request $request): JsonResponse
    {
        $organizationId = Auth::user()->organization_id;
        $perPage = $request->input('per_page', 15);
        $populations = $this->repository->getPaginatedPopulations($organizationId, $perPage);

        return response()->json([
            'error' => false,
            'message' => 'Dataset subject populations retrieved successfully',
            'data' => $populations,
        ]);
    }

    /**
     * Store a newly created dataset subject population.
     */
    public function store(StoreDatasetSubjectPopulationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = $request->user()->organization_id;
        $population = $this->repository->createPopulation($validated);

        return response()->json([
            'error' => false,
            'message' => 'Dataset subject population created successfully',
            'data' => new DatasetSubjectPopulationResource($population),
        ], 201);
    }

    /**
     * Display the specified dataset subject population.
     */
    public function show(DatasetSubjectPopulation $datasetSubjectPopulation): JsonResponse
    {
        return response()->json([
            'error' => false,
            'message' => 'Dataset subject population retrieved successfully',
            'data' => new DatasetSubjectPopulationResource($datasetSubjectPopulation),
        ]);
    }

    /**
     * Update the specified dataset subject population.
     */
    public function update(
        UpdateDatasetSubjectPopulationRequest $request,
        DatasetSubjectPopulation $datasetSubjectPopulation
    ): JsonResponse {
        $population = $this->repository->updatePopulation($datasetSubjectPopulation, $request->validated());

        return response()->json([
            'error' => false,
            'message' => 'Dataset subject population updated successfully',
            'data' => new DatasetSubjectPopulationResource($population),
        ]);
    }

    /**
     * Remove the specified dataset subject population.
     */
    public function destroy(DatasetSubjectPopulation $datasetSubjectPopulation): JsonResponse
    {
        $this->repository->deletePopulation($datasetSubjectPopulation);

        return response()->json([
            'error' => false,
            'message' => 'Dataset subject population deleted successfully',
            'data' => null,
        ]);
    }
}

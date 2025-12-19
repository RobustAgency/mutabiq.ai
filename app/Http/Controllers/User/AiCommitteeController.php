<?php

namespace App\Http\Controllers\User;

use App\Models\AiCommittee;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\AiCommitteeResource;
use App\Repositories\AiCommitteeRepository;
use App\Http\Requests\AiCommittee\ListAiCommitteeRequest;
use App\Http\Requests\AiCommittee\StoreAiCommitteeRequest;
use App\Http\Requests\AiCommittee\UpdateAiCommitteeRequest;

class AiCommitteeController extends Controller
{
    public function __construct(private AiCommitteeRepository $repository) {}

    /**
     * Get all AI committees with filtering and pagination.
     */
    public function index(ListAiCommitteeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $committees = $this->repository->getFilteredCommittees($validated);

        return response()->json([
            'data' => $committees,
            'message' => 'AI Committees retrieved successfully.',
            'error' => false,
        ]);
    }

    /**
     * Create a new AI committee.
     */
    public function store(StoreAiCommitteeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $committee = $this->repository->createCommittee($validated);

        return response()->json([
            'data' => new AiCommitteeResource($committee),
            'message' => 'AI Committee created successfully.',
            'error' => false,
        ], 201);
    }

    /**
     * Get a specific AI committee.
     */
    public function show(AiCommittee $aiCommittee): JsonResponse
    {
        return response()->json([
            'data' => new AiCommitteeResource($aiCommittee),
            'message' => 'AI Committee retrieved successfully.',
            'error' => false,
        ]);
    }

    /**
     * Update an existing AI committee.
     */
    public function update(UpdateAiCommitteeRequest $request, AiCommittee $aiCommittee): JsonResponse
    {
        $validated = $request->validated();
        $committee = $this->repository->updateCommittee($aiCommittee, $validated);

        return response()->json([
            'data' => new AiCommitteeResource($committee),
            'message' => 'AI Committee updated successfully.',
            'error' => false,
        ]);
    }

    /**
     * Delete an AI committee.
     */
    public function destroy(AiCommittee $aiCommittee): JsonResponse
    {
        $this->repository->deleteCommittee($aiCommittee);

        return response()->json([
            'data' => null,
            'message' => 'AI Committee deleted successfully.',
            'error' => false,
        ]);
    }
}

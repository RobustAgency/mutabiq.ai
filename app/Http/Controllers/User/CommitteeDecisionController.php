<?php

namespace App\Http\Controllers\User;

use App\Models\CommitteeDecision;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommitteeDecisionResource;
use App\Repositories\CommitteeDecisionRepository;
use App\Http\Requests\CommitteeDecision\ListCommitteeDecisionRequest;
use App\Http\Requests\CommitteeDecision\StoreCommitteeDecisionRequest;
use App\Http\Requests\CommitteeDecision\UpdateCommitteeDecisionRequest;

class CommitteeDecisionController extends Controller
{
    public function __construct(private CommitteeDecisionRepository $repository) {}

    public function index(ListCommitteeDecisionRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $decisions = $this->repository->getFilteredCommitteeDecisions($filters);

        return response()->json([
            'error' => false,
            'message' => 'Committee decisions retrieved successfully',
            'data' => $decisions,
        ]);
    }

    public function store(StoreCommitteeDecisionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $decision = $this->repository->createCommitteeDecision($data);

        return response()->json([
            'error' => false,
            'message' => 'Committee decision created successfully',
            'data' => new CommitteeDecisionResource($decision),
        ], 201);
    }

    public function show(CommitteeDecision $committeeDecision): JsonResponse
    {
        $committeeDecision->load('committeeMeeting');

        return response()->json([
            'error' => false,
            'message' => 'Committee decision retrieved successfully',
            'data' => new CommitteeDecisionResource($committeeDecision),
        ]);
    }

    public function update(UpdateCommitteeDecisionRequest $request, CommitteeDecision $committeeDecision): JsonResponse
    {
        $data = $request->validated();
        $decision = $this->repository->updateCommitteeDecision($committeeDecision, $data);

        return response()->json([
            'error' => false,
            'message' => 'Committee decision updated successfully',
            'data' => new CommitteeDecisionResource($decision),
        ]);
    }

    public function destroy(CommitteeDecision $committeeDecision): JsonResponse
    {
        $this->repository->deleteCommitteeDecision($committeeDecision);

        return response()->json([
            'error' => false,
            'message' => 'Committee decision deleted successfully',
        ]);
    }
}

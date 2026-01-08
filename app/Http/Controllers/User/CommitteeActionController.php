<?php

namespace App\Http\Controllers\User;

use App\Models\CommitteeAction;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommitteeActionResource;
use App\Repositories\CommitteeActionRepository;
use App\Http\Requests\CommitteeAction\ListCommitteeActionRequest;
use App\Http\Requests\CommitteeAction\StoreCommitteeActionRequest;
use App\Http\Requests\CommitteeAction\UpdateCommitteeActionRequest;

class CommitteeActionController extends Controller
{
    public function __construct(private CommitteeActionRepository $repository) {}

    public function index(ListCommitteeActionRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $actions = $this->repository->getFilteredCommitteeActions($filters);

        return response()->json([
            'error' => false,
            'message' => 'Committee actions retrieved successfully',
            'data' => $actions,
        ]);
    }

    public function store(StoreCommitteeActionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $action = $this->repository->createCommitteeAction($data);

        return response()->json([
            'error' => false,
            'message' => 'Committee action created successfully',
            'data' => new CommitteeActionResource($action),
        ], 201);
    }

    public function show(CommitteeAction $committeeAction): JsonResponse
    {
        $committeeAction->load(['committeeDecision', 'assignee']);

        return response()->json([
            'error' => false,
            'message' => 'Committee action retrieved successfully',
            'data' => new CommitteeActionResource($committeeAction),
        ]);
    }

    public function update(UpdateCommitteeActionRequest $request, CommitteeAction $committeeAction): JsonResponse
    {
        $data = $request->validated();
        $action = $this->repository->updateCommitteeAction($committeeAction, $data);

        return response()->json([
            'error' => false,
            'message' => 'Committee action updated successfully',
            'data' => new CommitteeActionResource($action),
        ]);
    }

    public function destroy(CommitteeAction $committeeAction): JsonResponse
    {
        $this->repository->deleteCommitteeAction($committeeAction);

        return response()->json([
            'error' => false,
            'message' => 'Committee action deleted successfully',
        ]);
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Models\CommitteeMeeting;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommitteeMeetingResource;
use App\Repositories\CommitteeMeetingRepository;
use App\Http\Requests\CommitteeMeeting\ListCommitteeMeetingRequest;
use App\Http\Requests\CommitteeMeeting\StoreCommitteeMeetingRequest;
use App\Http\Requests\CommitteeMeeting\UpdateCommitteeMeetingRequest;

class CommitteeMeetingController extends Controller
{
    public function __construct(private CommitteeMeetingRepository $repository) {}

    public function index(ListCommitteeMeetingRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $meetings = $this->repository->getFilteredCommitteeMeetings($filters);

        return response()->json([
            'error' => false,
            'message' => 'Committee meetings retrieved successfully',
            'data' => $meetings,
        ]);
    }

    public function store(StoreCommitteeMeetingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $meeting = $this->repository->createCommitteeMeeting($data);

        return response()->json([
            'error' => false,
            'message' => 'Committee meeting created successfully',
            'data' => new CommitteeMeetingResource($meeting),
        ], 201);
    }

    public function show(CommitteeMeeting $committeeMeeting): JsonResponse
    {
        $committeeMeeting->load('committee');

        return response()->json([
            'error' => false,
            'message' => 'Committee meeting retrieved successfully',
            'data' => new CommitteeMeetingResource($committeeMeeting),
        ]);
    }

    public function update(UpdateCommitteeMeetingRequest $request, CommitteeMeeting $committeeMeeting): JsonResponse
    {
        $data = $request->validated();
        $meeting = $this->repository->updateCommitteeMeeting($committeeMeeting, $data);

        return response()->json([
            'error' => false,
            'message' => 'Committee meeting updated successfully',
            'data' => new CommitteeMeetingResource($meeting),
        ]);
    }

    public function destroy(CommitteeMeeting $committeeMeeting): JsonResponse
    {
        $this->repository->deleteCommitteeMeeting($committeeMeeting);

        return response()->json([
            'error' => false,
            'message' => 'Committee meeting deleted successfully',
        ]);
    }
}

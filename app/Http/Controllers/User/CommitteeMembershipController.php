<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\JsonResponse;
use App\Models\CommitteeMembership;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommitteeMembershipResource;
use App\Repositories\CommitteeMembershipRepository;
use App\Http\Requests\CommitteeMembership\ListCommitteeMembershipRequest;
use App\Http\Requests\CommitteeMembership\StoreCommitteeMembershipRequest;
use App\Http\Requests\CommitteeMembership\UpdateCommitteeMembershipRequest;

class CommitteeMembershipController extends Controller
{
    public function __construct(private CommitteeMembershipRepository $repository) {}

    /**
     * Get all committee memberships with filtering and pagination.
     */
    public function index(ListCommitteeMembershipRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $memberships = $this->repository->getFilteredCommitteeMemberships($validated);

        return response()->json([
            'data' => $memberships,
            'message' => 'Committee memberships retrieved successfully.',
            'error' => false,
        ]);
    }

    /**
     * Create a new committee membership.
     */
    public function store(StoreCommitteeMembershipRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $membership = $this->repository->createCommitteeMembership($validated);

        return response()->json([
            'data' => new CommitteeMembershipResource($membership),
            'message' => 'Committee membership created successfully.',
            'error' => false,
        ], 201);
    }

    /**
     * Get a specific committee membership.
     */
    public function show(CommitteeMembership $committeeMembership): JsonResponse
    {
        return response()->json([
            'data' => new CommitteeMembershipResource($committeeMembership),
            'message' => 'Committee membership retrieved successfully.',
            'error' => false,
        ]);
    }

    /**
     * Update an existing committee membership.
     */
    public function update(UpdateCommitteeMembershipRequest $request, CommitteeMembership $committeeMembership): JsonResponse
    {
        $validated = $request->validated();
        $membership = $this->repository->updateCommitteeMembership($committeeMembership, $validated);

        return response()->json([
            'data' => new CommitteeMembershipResource($membership),
            'message' => 'Committee membership updated successfully.',
            'error' => false,
        ]);
    }

    /**
     * Delete a committee membership.
     */
    public function destroy(CommitteeMembership $committeeMembership): JsonResponse
    {
        $this->repository->deleteCommitteeMembership($committeeMembership);

        return response()->json([
            'data' => null,
            'message' => 'Committee membership deleted successfully.',
            'error' => false,
        ]);
    }
}

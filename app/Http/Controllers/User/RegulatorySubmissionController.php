<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\RegulatorySubmission;
use App\Http\Resources\RegulatorySubmissionResource;
use App\Repositories\RegulatorySubmissionRepository;
use App\Http\Requests\RegulatorySubmission\ListRegulatorySubmissionRequest;
use App\Http\Requests\RegulatorySubmission\StoreRegulatorySubmissionRequest;
use App\Http\Requests\RegulatorySubmission\UpdateRegulatorySubmissionRequest;

class RegulatorySubmissionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private RegulatorySubmissionRepository $regulatorySubmissionRepository
    ) {}

    /**
     * Display a paginated listing of regulatory submissions.
     */
    public function index(ListRegulatorySubmissionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $submissions = $this->regulatorySubmissionRepository->getFilteredRegulatorySubmissions($validated);

        return response()->json([
            'error' => false,
            'message' => 'Regulatory submissions retrieved successfully',
            'data' => $submissions,
        ]);
    }

    /**
     * Store a newly created regulatory submission record.
     */
    public function store(StoreRegulatorySubmissionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $submission = $this->regulatorySubmissionRepository->createRegulatorySubmission($validated);

        return response()->json([
            'error' => false,
            'message' => 'Regulatory submission created successfully',
            'data' => $submission,
        ], 201);
    }

    /**
     * Display the specified regulatory submission.
     */
    public function show(RegulatorySubmission $regulatorySubmission): JsonResponse
    {
        $regulatorySubmission->load([
            'framework',
            'aiModel',
            'submittedBy',
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Regulatory submission retrieved successfully',
            'data' => new RegulatorySubmissionResource($regulatorySubmission),
        ]);
    }

    /**
     * Update the specified regulatory submission.
     */
    public function update(UpdateRegulatorySubmissionRequest $request, RegulatorySubmission $regulatorySubmission): JsonResponse
    {
        $validated = $request->validated();
        $submission = $this->regulatorySubmissionRepository->updateRegulatorySubmission($regulatorySubmission, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Regulatory submission updated successfully',
            'data' => $submission,
        ]);
    }

    /**
     * Remove the specified regulatory submission.
     */
    public function destroy(RegulatorySubmission $regulatorySubmission): JsonResponse
    {
        $this->regulatorySubmissionRepository->deleteRegulatorySubmission($regulatorySubmission);

        return response()->json([
            'error' => false,
            'message' => 'Regulatory submission deleted successfully',
            'data' => null,
        ]);
    }
}

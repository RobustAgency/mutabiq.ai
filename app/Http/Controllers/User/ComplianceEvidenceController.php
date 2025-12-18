<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\JsonResponse;
use App\Models\ComplianceEvidence;
use App\Http\Controllers\Controller;
use App\Http\Resources\ComplianceEvidenceResource;
use App\Repositories\ComplianceEvidenceRepository;
use App\Http\Requests\ComplianceEvidence\ListComplianceEvidenceRequest;
use App\Http\Requests\ComplianceEvidence\StoreComplianceEvidenceRequest;
use App\Http\Requests\ComplianceEvidence\UpdateComplianceEvidenceRequest;

class ComplianceEvidenceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private ComplianceEvidenceRepository $complianceEvidenceRepository
    ) {}

    /**
     * Display a paginated listing of compliance evidence.
     */
    public function index(ListComplianceEvidenceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $evidence = $this->complianceEvidenceRepository->getFilteredComplianceEvidence($validated);

        return response()->json([
            'error' => false,
            'message' => 'Compliance evidence retrieved successfully',
            'data' => $evidence,
        ]);
    }

    /**
     * Store a newly created compliance evidence record.
     */
    public function store(StoreComplianceEvidenceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $evidence = $this->complianceEvidenceRepository->createComplianceEvidence($validated);

        return response()->json([
            'error' => false,
            'message' => 'Compliance evidence created successfully',
            'data' => $evidence,
        ], 201);
    }

    /**
     * Display the specified compliance evidence.
     */
    public function show(ComplianceEvidence $complianceEvidence): JsonResponse
    {
        $complianceEvidence->load([
            'control',
            'requirement',
            'aiModel',
            'collectedBy',
            'reviewedBy',
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Compliance evidence retrieved successfully',
            'data' => new ComplianceEvidenceResource($complianceEvidence),
        ]);
    }

    /**
     * Update the specified compliance evidence.
     */
    public function update(UpdateComplianceEvidenceRequest $request, ComplianceEvidence $complianceEvidence): JsonResponse
    {
        $validated = $request->validated();
        $evidence = $this->complianceEvidenceRepository->updateComplianceEvidence($complianceEvidence, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Compliance evidence updated successfully',
            'data' => $evidence,
        ]);
    }

    /**
     * Remove the specified compliance evidence.
     */
    public function destroy(ComplianceEvidence $complianceEvidence): JsonResponse
    {
        $this->complianceEvidenceRepository->deleteComplianceEvidence($complianceEvidence);

        return response()->json([
            'error' => false,
            'message' => 'Compliance evidence deleted successfully',
            'data' => null,
        ]);
    }
}

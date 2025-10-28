<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConsentCoverage\ListConsentCoverageRequest;
use App\Http\Requests\ConsentCoverage\StoreConsentCoverageRequest;
use App\Http\Requests\ConsentCoverage\UpdateConsentCoverageRequest;
use App\Http\Resources\ConsentCoverageResource;
use App\Models\ConsentCoverage;
use App\Repositories\ConsentCoverageRepository;
use Illuminate\Http\JsonResponse;

class ConsentCoverageController extends Controller
{
    public function __construct(private ConsentCoverageRepository $consentCoverageRepository) {}

    /**
     * Display a listing of consent coverages.
     */
    public function index(ListConsentCoverageRequest $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $coverages = $this->consentCoverageRepository->getPaginatedCoverages($perPage);

        return response()->json([
            'error' => false,
            'data' => $coverages,
            'message' => 'Consent coverages retrieved successfully'
        ]);
    }

    /**
     * Store a newly created consent coverage.
     */
    public function store(StoreConsentCoverageRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $coverage = $this->consentCoverageRepository->createCoverage($validated);

        return response()->json([
            'error' => false,
            'message' => 'Consent coverage created successfully',
            'data' => new ConsentCoverageResource($coverage)
        ], 201);
    }

    /**
     * Display the specified consent coverage.
     */
    public function show(ConsentCoverage $consentCoverage): JsonResponse
    {
        return response()->json([
            'error' => false,
            'data' => new ConsentCoverageResource($consentCoverage),
            'message' => 'Consent coverage retrieved successfully'
        ]);
    }

    /**
     * Update the specified consent coverage.
     */
    public function update(UpdateConsentCoverageRequest $request, ConsentCoverage $consentCoverage): JsonResponse
    {
        $validated = $request->validated();

        $this->consentCoverageRepository->updateCoverage($consentCoverage, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Consent coverage updated successfully',
            'data' => new ConsentCoverageResource($consentCoverage->fresh())
        ], 200);
    }

    /**
     * Remove the specified consent coverage.
     */
    public function destroy(ConsentCoverage $consentCoverage): JsonResponse
    {
        $this->consentCoverageRepository->deleteCoverage($consentCoverage);

        return response()->json([
            'error' => false,
            'message' => 'Consent coverage deleted successfully',
            'data' => null,
        ]);
    }
}

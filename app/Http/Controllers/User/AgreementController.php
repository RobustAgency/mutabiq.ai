<?php

namespace App\Http\Controllers\User;

use App\Models\Agreement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\AgreementResource;
use App\Repositories\AgreementRepository;
use App\Http\Requests\Agreement\StoreAgreementRequest;
use App\Http\Requests\Agreement\UpdateAgreementRequest;

class AgreementController extends Controller
{
    public function __construct(
        private readonly AgreementRepository $repository
    ) {}

    /**
     * Display a paginated listing of agreements.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $organizationID = $request->user()->organization_id;
        $agreements = $this->repository->getPaginatedAgreements($organizationID, $perPage);

        return response()->json([
            'error' => false,
            'message' => 'Agreements retrieved successfully',
            'data' => $agreements,
        ]);
    }

    /**
     * Store a newly created agreement.
     */
    public function store(StoreAgreementRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = $request->user()->organization_id;
        $agreement = $this->repository->createAgreement($validated);

        return response()->json([
            'error' => false,
            'message' => 'Agreement created successfully',
            'data' => new AgreementResource($agreement),
        ], 201);
    }

    /**
     * Display the specified agreement.
     */
    public function show(Agreement $agreement): JsonResponse
    {
        $agreement->load('vendor');

        return response()->json([
            'error' => false,
            'message' => 'Agreement retrieved successfully',
            'data' => new AgreementResource($agreement),
        ]);
    }

    /**
     * Update the specified agreement.
     */
    public function update(
        UpdateAgreementRequest $request,
        Agreement $agreement
    ): JsonResponse {
        $agreement = $this->repository->updateAgreement($agreement, $request->validated());

        return response()->json([
            'error' => false,
            'message' => 'Agreement updated successfully',
            'data' => new AgreementResource($agreement),
        ]);
    }

    /**
     * Remove the specified agreement.
     */
    public function destroy(Agreement $agreement): JsonResponse
    {
        $this->repository->deleteAgreement($agreement);

        return response()->json([
            'error' => false,
            'message' => 'Agreement deleted successfully',
            'data' => null,
        ]);
    }
}

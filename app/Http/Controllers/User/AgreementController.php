<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agreement\StoreAgreementRequest;
use App\Http\Requests\Agreement\UpdateAgreementRequest;
use App\Http\Resources\AgreementResource;
use App\Models\Agreement;
use App\Repositories\AgreementRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $agreements = $this->repository->getPaginatedAgreements($perPage);

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
        $agreement = $this->repository->createAgreement($request->validated());

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

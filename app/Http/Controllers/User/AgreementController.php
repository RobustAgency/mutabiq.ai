<?php

namespace App\Http\Controllers\User;

use App\Models\Agreement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
        $organizationID = Auth::user()->organization_id;
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
        $user = Auth::user();

        $validated = $request->validated();
        $validated['organization_id'] = $user->organization_id;
        $validated['created_by'] = $user->id;
        $validated['updated_by'] = $user->id;

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
    public function update(UpdateAgreementRequest $request, Agreement $agreement): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();
        $validated['updated_by'] = $user->id;
        $agreement = $this->repository->updateAgreement($agreement, $validated);

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

    /**
     * Get agreement statistics.
     */
    public function statistics(): JsonResponse
    {
        $organizationID = Auth::user()->organization_id;
        $stats = $this->repository->getStatistics($organizationID);

        return response()->json([
            'error' => false,
            'message' => 'Agreement statistics retrieved successfully',
            'data' => $stats,
        ]);
    }
}

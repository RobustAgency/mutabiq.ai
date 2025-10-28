<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConsentScope\ListConsentScopeRequest;
use App\Http\Requests\ConsentScope\StoreConsentScopeRequest;
use App\Http\Requests\ConsentScope\UpdateConsentScopeRequest;
use App\Http\Resources\ConsentScopeResource;
use App\Models\ConsentScope;
use App\Repositories\ConsentScopeRepository;
use Illuminate\Http\JsonResponse;

class ConsentScopeController extends Controller
{
    public function __construct(private ConsentScopeRepository $consentScopeRepository) {}

    /**
     * Display a listing of consent scopes.
     */
    public function index(ListConsentScopeRequest $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $consentScopes = $this->consentScopeRepository->getPaginatedConsentScopes($perPage);

        return response()->json([
            'error' => false,
            'data' => $consentScopes,
            'message' => 'Consent scopes retrieved successfully'
        ]);
    }

    /**
     * Store a newly created consent scope.
     */
    public function store(StoreConsentScopeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $consentScope = $this->consentScopeRepository->createConsentScope($validated);

        return response()->json([
            'error' => false,
            'message' => 'Consent scope created successfully',
            'data' => new ConsentScopeResource($consentScope)
        ], 201);
    }

    /**
     * Display the specified consent scope.
     */
    public function show(ConsentScope $consentScope): JsonResponse
    {
        return response()->json([
            'error' => false,
            'data' => new ConsentScopeResource($consentScope),
            'message' => 'Consent scope retrieved successfully'
        ]);
    }

    /**
     * Update the specified consent scope.
     */
    public function update(UpdateConsentScopeRequest $request, ConsentScope $consentScope): JsonResponse
    {
        $validated = $request->validated();

        $this->consentScopeRepository->updateConsentScope($consentScope, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Consent scope updated successfully',
            'data' => new ConsentScopeResource($consentScope)
        ], 200);
    }

    /**
     * Remove the specified consent scope.
     */
    public function destroy(ConsentScope $consentScope): JsonResponse
    {
        $this->consentScopeRepository->deleteConsentScope($consentScope);

        return response()->json([
            'error' => false,
            'message' => 'Consent scope deleted successfully',
            'data' => null,
        ]);
    }
}

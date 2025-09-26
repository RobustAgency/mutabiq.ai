<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\OrganizationResource;
use App\Repositories\OrganizationRepository;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Http\Requests\SearchOrganizationsRequest;

class OrganizationController extends Controller
{
    public function __construct(private OrganizationRepository $organizationRepository) {}

    public function index(SearchOrganizationsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $organizations = $this->organizationRepository->getFilteredOrganizations($validated);

        return response()->json([
            'error' => false,
            'message' => 'Organizations retrieved successfully',
            'data' => $organizations,
        ]);
    }

    public function show(Organization $organization): JsonResponse
    {
        $organization->load('members');

        return response()->json([
            'error' => false,
            'message' => 'Organization retrieved successfully',
            'data' => new OrganizationResource($organization),
        ]);
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        $validated = $request->validated();
        $organization = $this->organizationRepository->update($organization, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Organization updated successfully',
            'data' => null,
        ]);
    }
}

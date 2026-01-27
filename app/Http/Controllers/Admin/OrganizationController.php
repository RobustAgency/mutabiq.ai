<?php

namespace App\Http\Controllers\Admin;

use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use App\Events\OrganizationAdminCreated;
use App\Http\Resources\OrganizationResource;
use App\Repositories\OrganizationRepository;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Http\Requests\SearchOrganizationsRequest;
use App\Http\Requests\Admin\CreateAdminUserRequest;

class OrganizationController extends Controller
{
    public function __construct(
        private OrganizationRepository $organizationRepository,
        private UserRepository $userRepository
    ) {}

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

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $organization = $this->organizationRepository->create($validated);

        return response()->json([
            'error' => false,
            'message' => 'Organization created successfully',
            'data' => new OrganizationResource($organization),
        ], 201);
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
            'data' => new OrganizationResource($organization),
        ]);
    }

    public function destroy(Organization $organization): JsonResponse
    {
        $this->organizationRepository->delete($organization);

        return response()->json([
            'error' => false,
            'message' => 'Organization deleted successfully',
        ]);
    }

    public function storeAdmin(CreateAdminUserRequest $request, Organization $organization): JsonResponse
    {
        $validated = $request->validated();

        $admin = $this->userRepository->createAdminForOrganization($validated, $organization);

        event(new OrganizationAdminCreated($admin, $organization));

        return response()->json([
            'error' => false,
            'message' => 'Organization admin assigned successfully',
            'data' => new UserResource($admin),
        ], 201);
    }

    public function getAdmin(Organization $organization): JsonResponse
    {
        $admin = $this->userRepository->getAdminByOrganizationID($organization->id);

        return response()->json([
            'error' => false,
            'message' => 'Organization admin retrieved successfully',
            'data' => new UserResource($admin),
        ]);
    }
}

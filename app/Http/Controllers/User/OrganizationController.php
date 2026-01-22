<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrganizationResource;
use App\Repositories\OrganizationRepository;
use App\Http\Requests\StoreOrganizationRequest;

class OrganizationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private OrganizationRepository $organizationRepository) {}

    public function index(): JsonResponse
    {
        /** @var int $userID */
        $userID = Auth::id();

        $organization = $this->organizationRepository->getOrganizationWithMembersByUserID($userID);

        return response()->json([
            'error' => false,
            'message' => 'Organizations retrieved successfully',
            'data' => new OrganizationResource($organization),
        ]);
    }

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validated();

        $organization = DB::transaction(function () use ($validated, $user) {
            $organization = $this->organizationRepository->create($validated);

            $user->update([
                'organization_id' => $organization->id,
            ]);

            return $organization;
        });

        return response()->json([
            'error' => false,
            'message' => 'Organization created successfully',
            'data' => null,
        ], 201);
    }
}

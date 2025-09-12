<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Repositories\OrganizationRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreOrganizationRequest;

class OrganizationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private OrganizationRepository $organizationRepository) {}

    public function index() : JsonResponse
    {
        $userID = Auth::id();
        $organizations = $this->organizationRepository->getOrganizationWithMembersByUserID($userID);

        return response()->json([
            'error' => false,
            'message' => 'Organizations retrieved successfully',
            'data' => new OrganizationResource($organizations),
        ]);
    }

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $this->organizationRepository->createForUser($user, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Organization created successfully',
            'data' => null,
        ], 201);
    }
}

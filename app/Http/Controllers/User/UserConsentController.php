<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserConsent\ListUserConsentRequest;
use App\Http\Requests\UserConsent\StoreUserConsentRequest;
use App\Http\Requests\UserConsent\UpdateUserConsentRequest;
use App\Http\Resources\UserConsentResource;
use App\Models\UserConsent;
use App\Repositories\UserConsentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserConsentController extends Controller
{
    public function __construct(private UserConsentRepository $userConsentRepository) {}

    /**
     * Display a listing of user consents.
     */
    public function index(ListUserConsentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = Auth::user()->organization_id;

        $consents = $this->userConsentRepository->getFilteredUserConsents($validated);

        return response()->json([
            'error' => false,
            'data' => $consents,
            'message' => 'User consents retrieved successfully'
        ]);
    }

    /**
     * Store a newly created user consent.
     */
    public function store(StoreUserConsentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = Auth::user()->organization_id;

        $consent = $this->userConsentRepository->createConsent($validated);

        return response()->json([
            'error' => false,
            'message' => 'User consent created successfully',
            'data' => new UserConsentResource($consent)
        ], 201);
    }

    /**
     * Display the specified user consent.
     */
    public function show(UserConsent $userConsent): JsonResponse
    {
        return response()->json([
            'error' => false,
            'data' => new UserConsentResource($userConsent),
            'message' => 'User consent retrieved successfully'
        ]);
    }

    /**
     * Update the specified user consent.
     */
    public function update(UpdateUserConsentRequest $request, UserConsent $userConsent): JsonResponse
    {
        $validated = $request->validated();

        $this->userConsentRepository->updateConsent($userConsent, $validated);

        return response()->json([
            'error' => false,
            'message' => 'User consent updated successfully',
            'data' => new UserConsentResource($userConsent->fresh())
        ], 200);
    }

    /**
     * Remove the specified user consent.
     */
    public function destroy(UserConsent $userConsent): JsonResponse
    {
        $this->userConsentRepository->deleteConsent($userConsent);

        return response()->json([
            'error' => false,
            'message' => 'User consent deleted successfully',
            'data' => null,
        ]);
    }
}

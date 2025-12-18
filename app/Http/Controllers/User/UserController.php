<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(private UserRepository $userRepository) {}

    /**
     * Get all users in the authenticated user's organization.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $user = Auth::user();
        $organizationId = $user->organization_id;

        $users = $this->userRepository->getUsersByOrganizationID($organizationId, $validated['per_page'] ?? 15);

        return response()->json([
            'data' => $users,
            'message' => 'Organization users retrieved successfully.',
            'error' => false,
        ]);
    }
}

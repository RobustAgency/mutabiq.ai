<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Role\AssignRoleRequest;
use App\Http\Requests\Permission\AssignPermissionsRequest;
use App\Http\Requests\Permission\RevokePermissionsRequest;

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

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($user),
            'message' => 'User retrieved successfully.',
            'error' => false,
        ]);
    }

    public function assignRole(AssignRoleRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        $this->userRepository->assignRoleToUser($user, $validated);

        return response()->json([
            'data' => new UserResource($user),
            'message' => 'Role assigned to user successfully.',
            'error' => false,
        ]);
    }

    public function revokeRole(User $user, Role $role): JsonResponse
    {
        $this->userRepository->removeRoleFromUser($user, $role);

        return response()->json([
            'data' => new UserResource($user),
            'message' => 'Role revoked from user successfully.',
            'error' => false,
        ]);
    }

    public function assignPermission(AssignPermissionsRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        $permissionIds = $validated['permissions'];

        $this->userRepository->assignGranularPermissionsToUser($user, $permissionIds);

        return response()->json([
            'data' => new UserResource($user),
            'message' => 'Permission assigned to user successfully.',
            'error' => false,
        ]);
    }

    public function revokePermission(RevokePermissionsRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        $permissionIds = $validated['permissions'];

        $this->userRepository->revokeGranularPermissionsFromUser($user, $permissionIds);

        return response()->json([
            'data' => new UserResource($user),
            'message' => 'Permission revoked from user successfully.',
            'error' => false,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use App\Clients\SupabaseClient;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Validation\Rules\Enum;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\Admin\SearchUsersRequest;
use App\Http\Requests\Admin\CreateAdminUserRequest;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private UserRepository $userRepository,
        private SupabaseClient $supabaseClient
    ) {}

    /**
     * List all users with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['nullable', new Enum(UserRole::class)],
            'per_page' => 'integer|min:1|max:100',
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $role = $validated['role'] ?? null;

        $userRole = $role ? UserRole::tryFrom($role) : null;
        $users = $this->userRepository->getPaginatedByRole($userRole, $perPage);

        return response()->json([
            'error' => false,
            'message' => 'Users retrieved successfully',
            'data' => $users,
        ]);
    }

    /**
     * Search users based on criteria.
     */
    public function search(SearchUsersRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $users = $this->userRepository->search($validated);

        return response()->json([
            'error' => false,
            'message' => 'Users retrieved successfully',
            'data' => UserResource::collection($users),
        ]);
    }

    /**
     * Store a new admin user.
     */
    public function store(CreateAdminUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $supabaseResponse = $this->supabaseClient->createUser($data);
        $data['supabase_id'] = $supabaseResponse['id'];

        $user = User::registerUser($data);

        return response()->json([
            'error' => false,
            'message' => 'Admin user created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Show a specific user with their details.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'error' => false,
            'message' => 'User retrieved successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update user details.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        $this->supabaseClient->updateUser($user->supabase_id, $validated);
        $user->update($validated);

        return response()->json([
            'error' => false,
            'message' => 'User updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->supabaseClient->deleteUser($user->supabase_id);
        $user->delete();

        return response()->json([
            'error' => false,
            'message' => 'User deleted successfully',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Clients\SupabaseClient;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\Admin\SearchUsersRequest;
use App\Http\Requests\Admin\CreateAdminUserRequest;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private UserRepository $userRepository
    ) {}

    /**
     * List all users with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $users = $this->userRepository->getPaginated($perPage);

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

        $users = $this->userRepository->search($validated['term']);

        return response()->json([
            'error' => false,
            'message' => 'Users retrieved successfully',
            'data' => UserResource::collection($users),
        ]);
    }

    /**
     * Store a new admin user.
     */
    public function store(CreateAdminUserRequest $request, SupabaseClient $supabaseClient): JsonResponse
    {
        $data = $request->validated();

        $supabaseResponse = $supabaseClient->createUser($data);
        $data['supabase_id'] = $supabaseResponse['id'];

        $user = $this->userRepository->createAdmin($data);
        event(new Registered($user));

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
}

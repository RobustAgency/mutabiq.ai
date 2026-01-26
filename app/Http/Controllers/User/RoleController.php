<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Repositories\RoleRepository;
use App\Http\Requests\Role\ListRoleRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;

class RoleController extends Controller
{
    public function __construct(private RoleRepository $roleRepository) {}

    /**
     * Get all available roles.
     */
    public function index(ListRoleRequest $request): JsonResponse
    {
        $roles = $this->roleRepository->getFilteredRoles($request->validated());

        return response()->json([
            'error' => false,
            'message' => 'Roles retrieved successfully',
            'data' => $roles,
        ]);
    }

    /**
     * Get a specific role by ID.
     */
    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');

        return response()->json([
            'error' => false,
            'message' => 'Role retrieved successfully',
            'data' => new RoleResource($role),
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $role = $this->roleRepository->createRole($validated);

        return response()->json([
            'error' => false,
            'message' => 'Role created successfully',
            'data' => new RoleResource($role),
        ], 201);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $validated = $request->validated();

        $updatedRole = $this->roleRepository->updateRole($role, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Role updated successfully',
            'data' => new RoleResource($updatedRole),
        ]);
    }
}

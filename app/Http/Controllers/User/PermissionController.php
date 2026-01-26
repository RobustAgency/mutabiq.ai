<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\PermissionRepository;

class PermissionController extends Controller
{
    public function __construct(private PermissionRepository $permissionRepository) {}

    /**
     * Get all available permissions grouped by module and screen.
     */
    public function index(): JsonResponse
    {
        $permissions = $this->permissionRepository->getAllPermissions();

        return response()->json([
            'error' => false,
            'message' => 'All permissions retrieved successfully.',
            'data' => $permissions,
        ]);
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\PdpProcessingRegister\StorePdpProcessingRegisterRequest;
use App\Http\Requests\PdpProcessingRegister\UpdatePdpProcessingRegisterRequest;
use App\Http\Resources\PdpProcessingRegisterResource;
use App\Models\PdpProcessingRegister;
use App\Repositories\PdpProcessingRegisterRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PdpProcessingRegisterController extends Controller
{
    public function __construct(
        private readonly PdpProcessingRegisterRepository $repository
    ) {}

    /**
     * Display a paginated listing of PDP processing registers.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $registers = $this->repository->getPaginatedRegisters($perPage);

        return response()->json([
            'error' => false,
            'message' => 'PDP processing registers retrieved successfully',
            'data' => $registers,
        ]);
    }

    /**
     * Store a newly created PDP processing register.
     */
    public function store(StorePdpProcessingRegisterRequest $request): JsonResponse
    {
        $register = $this->repository->createRegister($request->validated());

        return response()->json([
            'error' => false,
            'message' => 'PDP processing register created successfully',
            'data' => new PdpProcessingRegisterResource($register),
        ], 201);
    }

    /**
     * Display the specified PDP processing register.
     */
    public function show(PdpProcessingRegister $pdpProcessingRegister): JsonResponse
    {
        return response()->json([
            'error' => false,
            'message' => 'PDP processing register retrieved successfully',
            'data' => new PdpProcessingRegisterResource($pdpProcessingRegister),
        ]);
    }

    /**
     * Update the specified PDP processing register.
     */
    public function update(
        UpdatePdpProcessingRegisterRequest $request,
        PdpProcessingRegister $pdpProcessingRegister
    ): JsonResponse {
        $register = $this->repository->updateRegister($pdpProcessingRegister, $request->validated());

        return response()->json([
            'error' => false,
            'message' => 'PDP processing register updated successfully',
            'data' => new PdpProcessingRegisterResource($register),
        ]);
    }

    /**
     * Remove the specified PDP processing register.
     */
    public function destroy(PdpProcessingRegister $pdpProcessingRegister): JsonResponse
    {
        $this->repository->deleteRegister($pdpProcessingRegister);

        return response()->json([
            'error' => false,
            'message' => 'PDP processing register deleted successfully',
            'data' => null,
        ]);
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\AiRiskRegister\StoreAiRiskRegisterRequest;
use App\Http\Requests\AiRiskRegister\UpdateAiRiskRegisterRequest;
use App\Http\Resources\AiRiskRegisterResource;
use App\Models\AiRiskRegister;
use App\Repositories\AiRiskRegisterRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AiRiskRegisterController extends Controller
{
    public function __construct(
        protected AiRiskRegisterRepository $repository
    ) {}

    /**
     * Display a listing of AI risk register entries.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page') ?? 15;
        $organizationID = Auth::user()->organization_id;
        $aiRiskRegister = $this->repository->getPaginatedAiRiskRegister($organizationID, $perPage);

        return response()->json([
            'error' => false,
            'message' => 'AI risk register entries retrieved successfully',
            'data' => $aiRiskRegister,
        ]);
    }

    /**
     * Store a newly created AI risk register entry.
     */
    public function store(StoreAiRiskRegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = Auth::user()->organization_id;

        $aiRiskRegister = $this->repository->createAiRiskRegister($validated);

        return response()->json([
            'error' => false,
            'message' => 'AI risk register entry created successfully',
            'data' => new AiRiskRegisterResource($aiRiskRegister),
        ], 201);
    }

    /**
     * Display the specified AI risk register entry.
     */
    public function show(AiRiskRegister $aiRiskRegister): JsonResponse
    {
        $aiRiskRegister = $this->repository->getAiRiskRegisterByID($aiRiskRegister);

        return response()->json([
            'error' => false,
            'message' => 'AI risk register entry retrieved successfully',
            'data' => new AiRiskRegisterResource($aiRiskRegister),
        ]);
    }

    /**
     * Update the specified AI risk register entry.
     */
    public function update(UpdateAiRiskRegisterRequest $request, AiRiskRegister $aiRiskRegister): JsonResponse
    {
        $validated = $request->validated();
        $aiRiskRegister = $this->repository->updateAiRiskRegister(
            $aiRiskRegister,
            $validated
        );

        return response()->json([
            'error' => false,
            'message' => 'AI risk register entry updated successfully',
            'data' => new AiRiskRegisterResource($aiRiskRegister),
        ]);
    }

    /**
     * Remove the specified AI risk register entry.
     */
    public function destroy(AiRiskRegister $aiRiskRegister): JsonResponse
    {
        $this->repository->deleteAiRiskRegister($aiRiskRegister);

        return response()->json([
            'error' => false,
            'message' => 'AI risk register entry deleted successfully',
            'data' => null,
        ]);
    }
}

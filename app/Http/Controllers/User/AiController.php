<?php

namespace App\Http\Controllers\User;

use App\Models\AiModel;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AiModelResource;
use App\Repositories\AiModelRepository;
use App\Http\Requests\ListAiModelRequest;
use App\Http\Requests\StoreAiModelRequest;

class AiController extends Controller
{
    public function __construct(private AiModelRepository $aiModelRepository) {}

    public function index(ListAiModelRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = Auth::user();
        $validated['organization_id'] = $user->organization_id;
        $aiModels = $this->aiModelRepository->getFilteredAiModels($validated);

        return response()->json([
            'error' => 'false',
            'data' => $aiModels,
        ], 200);
    }

    public function store(StoreAiModelRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user->organization_id) {
            return response()->json([
                'error' => 'true',
                'message' => 'User does not belong to any organization',
            ], 403);
        }

        $validated = $request->validated();
        $validated['organization_id'] = $user->organization_id;
        $validated['created_by'] = $user->id;
        $validated['updated_by'] = $user->id;

        $this->aiModelRepository->create($validated);

        return response()->json([
            'error' => 'false',
            'message' => 'AI Model created successfully',
        ], 201);
    }

    public function show(AiModel $aiModel): JsonResponse
    {
        return response()->json([
            'error' => 'false',
            'data' => new AiModelResource($aiModel),
        ], 200);
    }
}

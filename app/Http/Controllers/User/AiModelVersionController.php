<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAiModelVersionRequest;
use App\Http\Requests\UpdateAiModelVersionRequest;
use App\Http\Resources\AiModelVersionResource;
use App\Models\AiModelVersion;
use App\Repositories\AiModelVersionRepository;
use App\Http\Requests\ListAiModelVersionRequest;
use Illuminate\Http\JsonResponse;

class AiModelVersionController extends Controller
{
    public function __construct(private AiModelVersionRepository $aiModelVersionRepository) {}

    public function index(ListAiModelVersionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $aiModelVersions = $this->aiModelVersionRepository->getFilteredAiModelVersions($validated);

        return response()->json([
            'error' => false,
            'message' => 'AI Model Versions retrieved successfully',
            'data' => $aiModelVersions,
        ], 200);
    }

    public function store(StoreAiModelVersionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $this->aiModelVersionRepository->create($validated);

        return response()->json([
            'error' => false,
            'message' => 'AI Model Version created successfully',
        ], 201);
    }

    public function show(AiModelVersion $aiModelVersion): JsonResponse
    {
        return response()->json([
            'error' => false,
            'message' => 'AI Model Version retrieved successfully',
            'data' => new AiModelVersionResource($aiModelVersion),
        ], 200);
    }

    public function update(UpdateAiModelVersionRequest $request, AiModelVersion $aiModelVersion): JsonResponse
    {
        $validated = $request->validated();

        $this->aiModelVersionRepository->updateAiModelVersion($aiModelVersion, $validated);

        return response()->json([
            'error' => false,
            'message' => 'AI Model Version updated successfully',
        ], 200);
    }
}

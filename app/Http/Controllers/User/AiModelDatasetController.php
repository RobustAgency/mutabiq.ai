<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiModelDataset\ListAiModelDatasetRequest;
use App\Http\Requests\AiModelDataset\StoreAiModelDatasetRequest;
use App\Http\Requests\AiModelDataset\UpdateAiModelDatasetRequest;
use App\Http\Resources\AiModelDatasetResource;
use App\Models\AiModelDataset;
use App\Repositories\AiModelDatasetRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiModelDatasetController extends Controller
{
    public function __construct(private AiModelDatasetRepository $aiModelDatasetRepository) {}


    public function index(ListAiModelDatasetRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = $request->user()->organization_id;
        $aiModelDatasets = $this->aiModelDatasetRepository->getFilteredAiModelDatasets($validated);

        return response()->json([
            'error' => false,
            'data' => $aiModelDatasets,
            'message' => 'AI model datasets retrieved successfully'
        ], 200);
    }
    public function store(StoreAiModelDatasetRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = $request->user()->organization_id;

        $aiModelDataset = $this->aiModelDatasetRepository->create($validated);

        return response()->json([
            'error' => false,
            'message' => 'AI model dataset link created successfully',
            'data' => new AiModelDatasetResource($aiModelDataset)
        ], 201);
    }

    public function show(AiModelDataset $aiModelDataset): JsonResponse
    {
        $aiModelDataset->load(['aiModel', 'aiModelVersion', 'dataset']);
        return response()->json([
            'error' => false,
            'message' => 'AI model dataset retrieved successfully',
            'data' => new AiModelDatasetResource($aiModelDataset)
        ], 200);
    }

    public function update(UpdateAiModelDatasetRequest $request, AiModelDataset $aiModelDataset): JsonResponse
    {
        $validated = $request->validated();

        $this->aiModelDatasetRepository->update($aiModelDataset, $validated);

        return response()->json([
            'error' => false,
            'message' => 'AI model dataset link updated successfully',
            'data' => new AiModelDatasetResource($aiModelDataset)
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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


    public function index(Request $request): JsonResponse
    {
        $organizationId = Auth::user()->organization_id;
        $perPage = $request->input('per_page') ?? 15;
        $aiModelDatasets = $this->aiModelDatasetRepository->getPaginatedAiModelDatasets($organizationId, $perPage);

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

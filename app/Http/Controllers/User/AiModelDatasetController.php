<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiModelDataset\StoreAiModelDatasetRequest;
use App\Http\Resources\AiModelDatasetResource;
use App\Repositories\AiModelRepository;
use Illuminate\Http\JsonResponse;

class AiModelDatasetController extends Controller
{
    public function __construct(private AiModelRepository $aiModelRepository) {}

    /**
     * Store a newly created AI model dataset link.
     */
    public function store(StoreAiModelDatasetRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $aiModelDataset = $this->aiModelRepository->assignDataset($validated);

        return response()->json([
            'error' => false,
            'message' => 'AI model dataset link created successfully',
            'data' => new AiModelDatasetResource($aiModelDataset)
        ], 201);
    }
}

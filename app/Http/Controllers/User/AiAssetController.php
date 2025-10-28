<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AiAsset\StoreAiAssetRequest;
use App\Http\Requests\AiAsset\UpdateAiAssetRequest;
use App\Http\Resources\AiAssetResource;
use App\Models\AiAsset;
use App\Repositories\AiAssetRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiAssetController extends Controller
{
    public function __construct(
        private readonly AiAssetRepository $repository
    ) {}

    /**
     * Display a paginated listing of AI assets.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $aiAssets = $this->repository->getPaginatedAiAssets($perPage);

        return response()->json([
            'error' => false,
            'message' => 'AI assets retrieved successfully',
            'data' => $aiAssets,
        ]);
    }

    /**
     * Store a newly created AI asset.
     */
    public function store(StoreAiAssetRequest $request): JsonResponse
    {
        $aiAsset = $this->repository->createAiAsset($request->validated());

        return response()->json([
            'error' => false,
            'message' => 'AI asset created successfully',
            'data' => new AiAssetResource($aiAsset),
        ], 201);
    }

    /**
     * Display the specified AI asset.
     */
    public function show(AiAsset $aiAsset): JsonResponse
    {
        $aiAsset->load(['vendor', 'vendorAgreement']);

        return response()->json([
            'error' => false,
            'message' => 'AI asset retrieved successfully',
            'data' => new AiAssetResource($aiAsset),
        ]);
    }

    /**
     * Update the specified AI asset.
     */
    public function update(
        UpdateAiAssetRequest $request,
        AiAsset $aiAsset
    ): JsonResponse {
        $aiAsset = $this->repository->updateAiAsset($aiAsset, $request->validated());

        return response()->json([
            'error' => false,
            'message' => 'AI asset updated successfully',
            'data' => new AiAssetResource($aiAsset),
        ]);
    }

    /**
     * Remove the specified AI asset.
     */
    public function destroy(AiAsset $aiAsset): JsonResponse
    {
        $this->repository->deleteAiAsset($aiAsset);

        return response()->json([
            'error' => false,
            'message' => 'AI asset deleted successfully',
            'data' => null,
        ]);
    }
}

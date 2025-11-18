<?php

namespace App\Http\Controllers\User;

use App\Models\AiAsset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AiAssetResource;
use App\Repositories\AiAssetRepository;
use App\Http\Requests\AiAsset\StoreAiAssetRequest;
use App\Http\Requests\AiAsset\UpdateAiAssetRequest;

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
        $perPage = (int) $request->query('per_page', 15);
        $organizationID = (int) Auth::user()->organization_id;
        $aiAssets = $this->repository->getPaginatedAiAssets($organizationID, $perPage);

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
        $validated = $request->validated();
        $validated['organization_id'] = (int) Auth::user()->organization_id;
        $aiAsset = $this->repository->createAiAsset($validated);

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

<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListAiModelArtifactRequest;
use App\Http\Requests\StoreAiModelArtifactRequest;
use App\Http\Resources\AiModelArtifactResource;
use App\Models\AiModelArtifact;
use App\Repositories\AiModelArtifactRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiModelArtifactController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private AiModelArtifactRepository $repository,
    ) {}

    /**
     * Display a listing of the artifacts.
     */
    public function index(ListAiModelArtifactRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = Auth::user()->organization_id;
        $artifacts = $this->repository->getFilteredAiArtifacts($validated);

        return response()->json([
            'error' => false,
            'data' => $artifacts,
            'message' => 'AI Model Artifacts retrieved successfully'
        ]);
    }

    public function store(StoreAiModelArtifactRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['organization_id'] = Auth::user()->organization_id;
        $artifacts = $this->repository->createAiModelArtifact($data);
        return response()->json([
            'error' => false,
            'data' => new AiModelArtifactResource($artifacts),
            'message' => 'AI Model Artifact(s) created successfully'
        ]);
    }

    public function show(AiModelArtifact $aiModelArtifact): JsonResponse
    {
        return response()->json([
            'error' => false,
            'data' => new AiModelArtifactResource($aiModelArtifact),
            'message' => 'AI Model Artifact retrieved successfully'
        ]);
    }

    public function destroy(AiModelArtifact $aiModelArtifact): JsonResponse
    {
        $aiModelArtifact->delete();

        return response()->json([
            'error' => false,
            'message' => 'AI Model Artifact deleted successfully'
        ]);
    }
}

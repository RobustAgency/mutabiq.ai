<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAiModelArtifactRequest;
use App\Http\Requests\UpdateAiModelArtifactRequest;
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
        private AiModelArtifactRepository $repository
    ) {}

    /**
     * Display a listing of the artifacts.
     */
    public function index(Request $request): JsonResponse
    {
        $per_page = $request->input('per_page') ?? 15;
        $organizationID = Auth::user()->organization_id;
        $artifacts = $this->repository->getPaginatedArtifacts($organizationID, $per_page);

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
        $data['created_by'] = Auth::user()->id;
        $data['updated_by'] = Auth::user()->id;
        $this->repository->createAiModelArtifact($data);

        return response()->json([
            'error' => false,
            'message' => 'AI Model Artifact created successfully'
        ], 201);
    }

    public function show(AiModelArtifact $aiModelArtifact): JsonResponse
    {
        return response()->json([
            'error' => false,
            'data' => new AiModelArtifactResource($aiModelArtifact),
            'message' => 'AI Model Artifact retrieved successfully'
        ]);
    }

    public function update(UpdateAiModelArtifactRequest $request, AiModelArtifact $aiModelArtifact): JsonResponse
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::user()->email;
        $this->repository->updateAiModelArtifact($aiModelArtifact, $data);

        return response()->json([
            'error' => false,
            'message' => 'AI Model Artifact updated successfully'
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

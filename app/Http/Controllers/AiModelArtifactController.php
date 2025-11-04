<?php

namespace App\Http\Controllers;

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
        try {
            $result = $this->repository->createAiModelArtifact($data);

            return response()->json([
                'error' => $result['error'],
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to create AI Model Artifact: ' . $e->getMessage()
            ], 500);
        }
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

<?php

namespace App\Http\Controllers;

use App\Models\AiModelArtifact;
use App\Services\ChecksumService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Auth;
use App\Enums\ArtifactChecksumAlgorithm;
use App\Http\Resources\AiModelArtifactResource;
use App\Repositories\AiModelArtifactRepository;
use App\Http\Requests\ListAiModelArtifactRequest;
use App\Http\Requests\StoreAiModelArtifactRequest;

class AiModelArtifactController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private AiModelArtifactRepository $repository,
        private ChecksumService $checksumService,
        private FileUploadService $fileUploadService,
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
            'message' => 'AI Model Artifacts retrieved successfully',
        ]);
    }

    public function store(StoreAiModelArtifactRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['organization_id'] = Auth::id() ? Auth::user()->organization_id : null;
        $uploadedFile = $request->file('file');

        if ($uploadedFile instanceof UploadedFile) {
            $data['file'] = $this->fileUploadService->uploadFile($uploadedFile, 'ai_model_artifacts');
        }

        $algorithm = $data['checksum_algorithm'] ?? ArtifactChecksumAlgorithm::NONE->value;

        if ($algorithm !== ArtifactChecksumAlgorithm::NONE->value) {
            if (! empty($data['file']) && $uploadedFile instanceof UploadedFile) {
                $data['checksum_value'] = $this->checksumService->generateChecksum($algorithm, $data['file']);
            }
        }

        $artifact = $this->repository->createAiModelArtifact($data);

        return response()->json([
            'error' => false,
            'data' => new AiModelArtifactResource($artifact),
            'message' => 'AI Model Artifact created successfully',
        ], 201);
    }

    public function show(AiModelArtifact $aiModelArtifact): JsonResponse
    {
        return response()->json([
            'error' => false,
            'data' => new AiModelArtifactResource($aiModelArtifact),
            'message' => 'AI Model Artifact retrieved successfully',
        ]);
    }

    public function destroy(AiModelArtifact $aiModelArtifact): JsonResponse
    {
        $aiModelArtifact->delete();

        return response()->json([
            'error' => false,
            'message' => 'AI Model Artifact deleted successfully',
        ]);
    }
}

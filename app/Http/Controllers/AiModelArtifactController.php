<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportAiModelArtifactsRequest;
use App\Http\Requests\StoreAiModelArtifactRequest;
use App\Http\Requests\UpdateAiModelArtifactRequest;
use App\Http\Resources\AiModelArtifactResource;
use App\Models\AiModelArtifact;
use App\Repositories\AiModelArtifactRepository;
use App\Services\AiModelArtifactImportService;
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
            $statusCode = $result['success'] ? 200 : 422;

            return response()->json([
                'error' => !$result['success'],
                'message' => $result['message'],
                'data' => $result['data'],
            ], $statusCode);
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

    /**
     * Bulk import AI Model Artifacts from CSV or Excel file
     */
    public function import(ImportAiModelArtifactsRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');
            $artifactType = $request->input('artifact_type');
            $organizationId = Auth::user()->organization_id;
            $userId = Auth::user()->id;

            $result = $this->importService->import(
                $file,
                $organizationId,
                $artifactType,
                $userId,
                $userId
            );

            $statusCode = $result['success'] ? 200 : 422;

            return response()->json([
                'error' => !$result['success'],
                'message' => $result['message'],
                'data' => $result['data'],
            ], $statusCode);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred during import: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download import template
     */
    public function downloadTemplate(): JsonResponse
    {
        try {
            $path = $this->importService->generateTemplate();
            $filename = basename($path);

            return response()->json([
                'error' => false,
                'message' => 'Template generated successfully',
                'data' => [
                    'download_url' => url('storage/templates/' . $filename),
                    'filename' => $filename,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to generate template: ' . $e->getMessage(),
            ], 500);
        }
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

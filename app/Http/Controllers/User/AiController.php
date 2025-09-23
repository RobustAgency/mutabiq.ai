<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAiModelRequest;
use App\Http\Resources\AiModelResource;
use App\Models\AiModel;
use App\Repositories\AiModelRepository;
use Illuminate\Support\Facades\Auth;

class AiController extends Controller
{
    public function __construct(private AiModelRepository $aiModelRepository) {}

    public function index()
    {
        $user = Auth::user();
        if (! $user->organization_id) {
            return response()->json([
                'error' => 'true',
                'message' => 'User does not belong to any organization'
            ], 403);
        }
        $aiModels = $this->aiModelRepository->getAllAiModelsByOrganizationID($user->organization_id);
        
        return response()->json([
            'error' => 'false',
            'data' => $aiModels
        ], 200);
    }

    public function store(StoreAiModelRequest $request)
    {
        $user = Auth::user();
        if (! $user->organization_id) {
            return response()->json([
                'error' => 'true',
                'message' => 'User does not belong to any organization'
            ], 403);
        }

        $validated = $request->validated();
        $validated['organization_id'] = $user->organization_id;
        $validated['created_by'] = $user->id;
        $validated['updated_by'] = $user->id;

        $this->aiModelRepository->create($validated);

        return response()->json([
            'error' => 'false',
            'message' => 'AI Model created successfully'
        ], 201);
    }

    public function show(AiModel $aiModel)
    {
        $aiModelID = $aiModel->id;
        $aiModel = $this->aiModelRepository->getAiModelByID($aiModelID);

        return response()->json([
            'error' => 'false',
            'data' => new AiModelResource($aiModel),
        ], 200);
    }
}

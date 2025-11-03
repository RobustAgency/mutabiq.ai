<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchAiModelUseCaseRequest;
use App\Http\Requests\StoreAiModelUseCaseRequest;
use App\Http\Requests\UpdateAiModelUseCaseRequest;
use App\Http\Resources\AiModelUseCaseResource;
use App\Models\AiModelUseCase;
use App\Repositories\AiModelUseCaseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AiModelUseCaseController extends Controller
{
    public function __construct(private AiModelUseCaseRepository $aiModelUseCaseRepository) {}

    public function index(SearchAiModelUseCaseRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $filters['organization_id'] = Auth::user()->organization_id;
        $aiModelUseCases = $this->aiModelUseCaseRepository->getFilteredAiModelUseCases($filters);
        return response()->json([
            'error' => false,
            'message' => 'AI Model Use Case associations retrieved successfully',
            'data' => $aiModelUseCases,
        ], 200);
    }

    public function store(StoreAiModelUseCaseRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();
        $validated['organization_id'] = $user->organization_id;
        $this->aiModelUseCaseRepository->createAiModelUseCase($user, $validated);
        return response()->json([
            'error' => false,
            'message' => 'AI Model Use Case association created successfully',
            'data' => null,
        ], 201);
    }

    public function show(AiModelUseCase $aiModelUseCase): JsonResponse
    {
        $aiModelUseCase->load([
            'aiModel',
            'useCase',
            'aiModelVersion',
            'createdBy',
            'updatedBy'
        ]);
        return response()->json([
            'error' => false,
            'message' => 'AI Model Use Case association retrieved successfully',
            'data' => new AiModelUseCaseResource($aiModelUseCase),
        ], 200);
    }

    public function update(UpdateAiModelUseCaseRequest $request, AiModelUseCase $aiModelUseCase): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();
        $this->aiModelUseCaseRepository->updateAiModelUseCase($aiModelUseCase, $user, $validated);
        return response()->json([
            'error' => false,
            'message' => 'AI Model Use Case association updated successfully',
            'data' => null,
        ], 200);
    }

    public function destroy(AiModelUseCase $aiModelUseCase): JsonResponse
    {
        $aiModelUseCase->delete();
        return response()->json([
            'error' => false,
            'message' => 'AI Model Use Case association deleted successfully',
            'data' => null,
        ], 200);
    }
}

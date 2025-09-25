<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchAiModelUseCaseRequest;
use App\Http\Requests\StoreAiModelUseCaseRequest;
use App\Models\AiModelUseCase;
use App\Repositories\AiModelUseCaseRepository;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\AiModelUseCaseResource;

class AiModelUseCaseController extends Controller
{
    public function __construct(private AiModelUseCaseRepository $repository) {}

    public function index(SearchAiModelUseCaseRequest $request): JsonResponse
    {
        $useCases = $this->repository->getFilteredAiModelUseCases($request->validated());
        return response()->json([
            'data' => $useCases,
            'error' => false,
            'message' => 'AI Model Use Cases retrieved successfully'
        ], 200);
    }

    public function store(StoreAiModelUseCaseRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->repository->createAiModelUseCase($data);

        return response()->json([
            'data' => null,
            'error' => false,
            'message' => 'AI Model Use Case created successfully'
        ], 201);
    }

    public function show(AiModelUseCase $aiModelUseCase): JsonResponse
    {
        return response()->json([
            'data' => new AiModelUseCaseResource($aiModelUseCase),
            'error' => false,
            'message' => 'AI Model Use Case retrieved successfully'
        ], 200);
    }
}

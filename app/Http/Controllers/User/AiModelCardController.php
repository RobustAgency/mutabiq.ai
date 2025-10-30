<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListAiModelCardRequest;
use App\Http\Requests\StoreAiModelCardRequest;
use App\Http\Requests\UpdateAiModelCardRequest;
use App\Http\Resources\AiModelCardResource;
use App\Models\AiModelCard;
use App\Repositories\AiModelCardRepository;
use Illuminate\Http\JsonResponse;

class AiModelCardController extends Controller
{
    public function __construct(private AiModelCardRepository $aiModelCardRepository) {}

    public function index(ListAiModelCardRequest $request): JsonResponse
    {
        $aiModelID = $request->input('ai_model_id');
        $perPage = $request->input('per_page') ?? 15;
        $aiModelCards = $this->aiModelCardRepository->getPaginatedAiModelCards(
            $aiModelID,
            $perPage
        );
        return response()->json([
            'error' => false,
            'data' => $aiModelCards,
            'message' => 'AI Model Cards retrieved successfully'
        ]);
    }
    public function store(StoreAiModelCardRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->aiModelCardRepository->createAiModelCard($data);

        return response()->json([
            'error' => false,
            'message' => 'AI Model Card created successfully'
        ], 201);
    }

    public function show(AiModelCard $aiModelCard): JsonResponse
    {
        $aiModelCard = $this->aiModelCardRepository->getAiModelCardById($aiModelCard);

        return response()->json([
            'error' => false,
            'data' => new AiModelCardResource($aiModelCard),
            'message' => 'AI Model Card retrieved successfully'
        ]);
    }

    public function update(UpdateAiModelCardRequest $request, AiModelCard $aiModelCard): JsonResponse
    {
        $data = $request->validated();
        $this->aiModelCardRepository->updateAiModelCard($aiModelCard, $data);

        return response()->json([
            'error' => false,
            'message' => 'AI Model Card updated successfully'
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAiModelCardRequest;
use App\Http\Requests\UpdateAiModelCardRequest;
use App\Models\AiModelCard;
use App\Repositories\AiModelCardRepository;
use Illuminate\Http\JsonResponse;

class AiModelCardController extends Controller
{
    public function __construct(private AiModelCardRepository $aiModelCardRepository) {}

    public function store(StoreAiModelCardRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->aiModelCardRepository->createAiModelCard($data);

        return response()->json([
            'error' => false,
            'message' => 'AI Model Card created successfully'
        ], 201);
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

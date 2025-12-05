<?php

namespace App\Http\Controllers\User;

use App\Models\AiRiskTreatment;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AiRiskTreatmentResource;
use App\Repositories\AiRiskTreatmentRepository;
use App\Http\Requests\AiRiskTreatment\ListAiRiskTreatmentRequest;
use App\Http\Requests\AiRiskTreatment\StoreAiRiskTreatmentRequest;
use App\Http\Requests\AiRiskTreatment\UpdateAiRiskTreatmentRequest;

class AiRiskTreatmentController extends Controller
{
    public function __construct(private AiRiskTreatmentRepository $repository) {}

    public function index(ListAiRiskTreatmentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = Auth::user();
        $validated['organization_id'] = $user->organization_id;
        $treatments = $this->repository->getFilteredAiRiskTreatments($validated);

        return response()->json([
            'data' => $treatments,
            'message' => 'AI Risk Treatments retrieved successfully.',
            'error' => false,
        ]);
    }

    public function store(StoreAiRiskTreatmentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = Auth::user();
        $validated['organization_id'] = $user->organization_id;
        $treatment = $this->repository->createAiRiskTreatment($validated);

        return response()->json([
            'data' => $treatment,
            'message' => 'AI Risk Treatment created successfully.',
            'error' => false,
        ]);
    }

    public function show(AiRiskTreatment $aiRiskTreatment): JsonResponse
    {
        return response()->json([
            'data' => new AiRiskTreatmentResource($aiRiskTreatment),
            'message' => 'AI Risk Treatment retrieved successfully.',
            'error' => false,
        ]);
    }

    public function update(UpdateAiRiskTreatmentRequest $request, AiRiskTreatment $aiRiskTreatment): JsonResponse
    {
        $validated = $request->validated();

        $this->repository->updateAiRiskTreatment($aiRiskTreatment, $validated);

        return response()->json([
            'data' => null,
            'message' => 'AI Risk Treatment updated successfully.',
            'error' => false,
        ]);
    }

    public function destroy(AiRiskTreatment $aiRiskTreatment): JsonResponse
    {
        $this->repository->deleteAiRiskTreatment($aiRiskTreatment);

        return response()->json([
            'data' => null,
            'message' => 'AI Risk Treatment deleted successfully.',
            'error' => false,
        ]);
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Models\RiskMethodology;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\RiskMethodologyRepository;
use App\Http\Requests\RiskMethodology\ListRiskMethodologyRequest;
use App\Http\Requests\RiskMethodology\StoreRiskMethodologyRequest;
use App\Http\Requests\RiskMethodology\UpdateRiskMethodologyRequest;

class RiskMethodologyController extends Controller
{
    public function __construct(private RiskMethodologyRepository $riskMethodologyRepository) {}

    public function index(ListRiskMethodologyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = Auth::user();
        $validated['organization_id'] = $user->organization_id;

        $riskMethodologies = $this->riskMethodologyRepository->getFilteredRiskMethodologies($validated);

        return response()->json([
            'data' => $riskMethodologies,
            'message' => 'Risk Methodologies retrieved successfully.',
            'error' => false,
        ]);
    }

    public function store(StoreRiskMethodologyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = Auth::user();
        $validated['organization_id'] = $user->organization_id;

        $riskMethodology = $this->riskMethodologyRepository->createRiskMethodology($validated);

        return response()->json([
            'data' => $riskMethodology,
            'message' => 'Risk Methodology created successfully.',
            'error' => false,
        ], 201);
    }

    public function show(RiskMethodology $riskMethodology): JsonResponse
    {
        $riskMethodology = $this->riskMethodologyRepository->getRiskMethodology($riskMethodology);

        return response()->json([
            'data' => $riskMethodology,
            'message' => 'Risk Methodology retrieved successfully.',
            'error' => false,
        ]);
    }

    public function update(UpdateRiskMethodologyRequest $request, RiskMethodology $riskMethodology): JsonResponse
    {
        $validated = $request->validated();

        $updatedRiskMethodology = $this->riskMethodologyRepository->updateRiskMethodology($riskMethodology, $validated);

        return response()->json([
            'data' => $updatedRiskMethodology,
            'message' => 'Risk Methodology updated successfully.',
            'error' => false,
        ]);
    }

    public function destroy(RiskMethodology $riskMethodology): JsonResponse
    {
        $this->riskMethodologyRepository->deleteRiskMethodology($riskMethodology);

        return response()->json([
            'message' => 'Risk Methodology deleted successfully.',
            'error' => false,
        ]);
    }
}

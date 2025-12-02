<?php

namespace App\Http\Controllers\User;

use App\Models\KriIndicator;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\KriIndicatorResource;
use App\Repositories\KriIndicatorRepository;
use App\Http\Requests\KriIndicator\ListKriIndicatorRequest;
use App\Http\Requests\KriIndicator\StoreKriIndicatorRequest;

class KriIndicatorController extends Controller
{
    public function __construct(private KriIndicatorRepository $kriIndicatorRepository) {}

    public function index(ListKriIndicatorRequest $request): JsonResponse
    {
        $user = Auth::user();
        $filters = $request->validated();
        $filters['organization_id'] = $user->organization_id;

        $kriIndicators = $this->kriIndicatorRepository->getFilteredKriIndicators($filters);

        return response()->json([
            'data' => $kriIndicators,
            'message' => 'KRI Indicators retrieved successfully',
            'error' => false,
        ]);
    }

    public function store(StoreKriIndicatorRequest $request): JsonResponse
    {
        $user = Auth::user();

        $data = $request->validated();
        $data['organization_id'] = $user->organization_id;
        $data['created_by'] = $user->id;

        $kriIndicator = $this->kriIndicatorRepository->createKriIndicator($data);

        return response()->json([
            'data' => $kriIndicator,
            'message' => 'KRI Indicator created successfully',
            'error' => false,
        ]);
    }

    public function show(KriIndicator $kriIndicator): JsonResponse
    {
        $kriIndicator->load(['organization', 'aiRiskRegister', 'createdBy']);

        return response()->json([
            'data' => new KriIndicatorResource($kriIndicator),
            'message' => 'KRI Indicator retrieved successfully',
            'error' => false,
        ]);
    }

    public function update(StoreKriIndicatorRequest $request, KriIndicator $kriIndicator): JsonResponse
    {
        $data = $request->validated();

        $updatedKriIndicator = $this->kriIndicatorRepository->updateKriIndicator($kriIndicator, $data);

        return response()->json([
            'data' => $updatedKriIndicator,
            'message' => 'KRI Indicator updated successfully',
            'error' => false,
        ]);
    }

    public function destroy(KriIndicator $kriIndicator): JsonResponse
    {
        $this->kriIndicatorRepository->deleteKriIndicator($kriIndicator);

        return response()->json([
            'data' => null,
            'message' => 'KRI Indicator deleted successfully',
            'error' => false,
        ]);
    }
}

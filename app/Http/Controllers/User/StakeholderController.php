<?php

namespace App\Http\Controllers\User;

use App\Models\Stakeholder;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\StakeholderResource;
use App\Repositories\StakeholderRepository;
use App\Http\Requests\ListStakeholderRequest;
use App\Http\Requests\StoreStakeholderRequest;
use App\Http\Requests\UpdateStakeholderRequest;

class StakeholderController extends Controller
{
    public function __construct(private StakeholderRepository $stakeholderRepository) {}

    public function index(ListStakeholderRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $filters['organization_id'] = Auth::user()->organization_id;
        $stakeholders = $this->stakeholderRepository->getFilteredStakeholders($filters);

        return response()->json([
            'error' => false,
            'message' => 'Stakeholders retrieved successfully.',
            'data' => $stakeholders,
        ]);
    }

    public function statistics(): JsonResponse
    {
        $stats = $this->stakeholderRepository->getStatistics(Auth::user()->organization_id);

        return response()->json([
            'error' => false,
            'message' => 'Stakeholder statistics retrieved successfully.',
            'data' => $stats,
        ]);
    }

    public function store(StoreStakeholderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = Auth::user()->organization_id;
        $stakeholder = $this->stakeholderRepository->create($validated);

        return response()->json([
            'error' => false,
            'message' => 'Stakeholder created successfully.',
            'data' => new StakeholderResource($stakeholder),
        ]);
    }

    public function show(Stakeholder $stakeholder): JsonResponse
    {
        return response()->json([
            'error' => false,
            'message' => 'Stakeholder retrieved successfully.',
            'data' => new StakeholderResource($stakeholder),
        ]);
    }

    public function update(UpdateStakeholderRequest $request, Stakeholder $stakeholder): JsonResponse
    {
        $validated = $request->validated();
        $stakeholder = $this->stakeholderRepository->update($stakeholder, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Stakeholder updated successfully.',
            'data' => new StakeholderResource($stakeholder),
        ]);
    }

    public function destroy(Stakeholder $stakeholder): JsonResponse
    {
        $stakeholder->delete();

        return response()->json([
            'error' => false,
            'message' => 'Stakeholder deleted successfully.',
            'data' => null,
        ]);
    }
}

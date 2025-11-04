<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListStakeholderRequest;
use App\Http\Requests\StoreStakeholderRequest;
use App\Models\Stakeholder;
use App\Repositories\StakeholderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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

    public function store(StoreStakeholderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = Auth::user()->organization_id;
        $stakeholder = $this->stakeholderRepository->create($validated);

        return response()->json([
            'error' => false,
            'message' => 'Stakeholder created successfully.',
            'data' => $stakeholder,
        ]);
    }

    public function show(Stakeholder $stakeholder): JsonResponse
    {
        return response()->json([
            'error' => false,
            'message' => 'Stakeholder retrieved successfully.',
            'data' => $stakeholder,
        ]);
    }

    public function update(StoreStakeholderRequest $request, Stakeholder $stakeholder): JsonResponse
    {
        $validated = $request->validated();
        $stakeholder = $this->stakeholderRepository->update($stakeholder, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Stakeholder updated successfully.',
            'data' => null,
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

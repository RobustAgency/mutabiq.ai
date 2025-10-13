<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStakeholderRequest;
use App\Models\Stakeholder;
use App\Repositories\StakeholderRepository;
use Illuminate\Http\JsonResponse;

class StakeholderController extends Controller
{
    public function __construct(private StakeholderRepository $stakeholderRepository) {}

    public function index(): JsonResponse
    {
        $filters = request()->only(['type', 'per_page']);
        $stakeholders = $this->stakeholderRepository->getFilteredStakeholders($filters);

        return response()->json($stakeholders);
    }

    public function store(StoreStakeholderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $stakeholder = $this->stakeholderRepository->create($validated);

        return response()->json([
            'error' => false,
            'message' => 'Stakeholder created successfully.',
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
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchUseCaseRequest;
use App\Http\Requests\StoreUseCaseRequest;
use App\Models\UseCase;
use App\Repositories\UseCaseRepository;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UseCaseResource;
use Illuminate\Support\Facades\Auth;

class UseCaseController extends Controller
{
    public function __construct(private UseCaseRepository $repository) {}

    public function index(SearchUseCaseRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $filters['organization_id'] = Auth::user()->organization_id;
        $useCases = $this->repository->getFilteredUseCases($filters);
        return response()->json([
            'data' => $useCases,
            'error' => false,
            'message' => 'Use Cases retrieved successfully'
        ], 200);
    }

    public function store(StoreUseCaseRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['organization_id'] = $request->user()->organization_id;
        $this->repository->createUseCase($data);

        return response()->json([
            'data' => null,
            'error' => false,
            'message' => 'Use Case created successfully'
        ], 201);
    }

    public function show(UseCase $useCase): JsonResponse
    {
        return response()->json([
            'data' => new UseCaseResource($useCase),
            'error' => false,
            'message' => 'Use Case retrieved successfully'
        ], 200);
    }
}

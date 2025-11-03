<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\DataElement\ListDataElementRequest;
use App\Http\Requests\DataElement\StoreDataElementRequest;
use App\Http\Requests\DataElement\UpdateDataElementRequest;
use App\Models\DataElement;
use App\Repositories\DataElementRepository;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\DataElementResource;
use Illuminate\Support\Facades\Auth;

class DataElementController extends Controller
{
    public function __construct(
        private readonly DataElementRepository $repository
    ) {}

    public function index(ListDataElementRequest $request): JsonResponse
    {
        $organizationId = Auth::user()->organization_id;
        $perPage = $request->validated('per_page', 15);
        $dataElements = $this->repository->getPaginatedDataElements($organizationId, $perPage);

        return response()->json([
            'error' => false,
            'message' => 'Data elements retrieved successfully',
            'data' => $dataElements,
        ]);
    }

    public function store(StoreDataElementRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = $request->user()->organization_id;
        $dataElement = $this->repository->createDataElement($validated);

        return response()->json([
            'error' => false,
            'message' => 'Data element created successfully',
            'data' => new DataElementResource($dataElement),
        ], 201);
    }

    public function show(DataElement $dataElement): JsonResponse
    {
        $dataElement = $this->repository->getDataElementByID($dataElement->id);

        return response()->json([
            'error' => false,
            'message' => 'Data element retrieved successfully',
            'data' => new DataElementResource($dataElement),
        ]);
    }

    public function update(UpdateDataElementRequest $request, DataElement $dataElement): JsonResponse
    {
        $updatedDataElement = $this->repository->updateDataElement($dataElement, $request->validated());

        return response()->json([
            'error' => false,
            'message' => 'Data element updated successfully',
            'data' => new DataElementResource($updatedDataElement),
        ]);
    }

    public function destroy(DataElement $dataElement): JsonResponse
    {
        $this->repository->delete($dataElement);

        return response()->json([
            'error' => false,
            'message' => 'Data element deleted successfully',
            'data' => null,
        ]);
    }
}

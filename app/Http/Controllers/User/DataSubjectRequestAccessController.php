<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\DataSubjectRequestAccess;
use App\Http\Resources\DataSubjectRequestAccessResource;
use App\Repositories\DataSubjectRequestAccessRepository;
use App\Enums\DataSubjectRequestAccess\VerificationStatus;
use App\Http\Requests\DataSubjectRequestAccess\ListDataSubjectRequestAccessRequest;
use App\Http\Requests\DataSubjectRequestAccess\StoreDataSubjectRequestAccessRequest;
use App\Http\Requests\DataSubjectRequestAccess\UpdateDataSubjectRequestAccessRequest;

class DataSubjectRequestAccessController extends Controller
{
    public function __construct(private DataSubjectRequestAccessRepository $repository) {}

    public function index(ListDataSubjectRequestAccessRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $dataSubjectRequests = $this->repository->getFilteredDataSubjectRequestAccesses($filters);

        return response()->json([
            'error' => false,
            'message' => 'Data Subject Requests retrieved successfully',
            'data' => $dataSubjectRequests,
        ]);
    }

    public function store(StoreDataSubjectRequestAccessRequest $request): JsonResponse
    {
        $data = $request->validated();

        $uuid = Str::uuid()->toString();
        $data['request_code'] = 'DSAR-'.date('Y').'-'.$uuid;
        if (isset($data['verification_status']) && $data['verification_status'] === VerificationStatus::VERIFIED->value) {
            $data['verification_date'] = now();
        }
        $dataSubjectRequestAccess = $this->repository->createDataSubjectRequestAccess($data);

        return response()->json([
            'error' => false,
            'message' => 'Data Subject Request Access created successfully',
            'data' => $dataSubjectRequestAccess,
        ], 201);

    }

    public function show(DataSubjectRequestAccess $dataSubjectRequestAccess): JsonResponse
    {
        return response()->json([
            'error' => false,
            'message' => 'Data Subject Request Access retrieved successfully',
            'data' => new DataSubjectRequestAccessResource($dataSubjectRequestAccess),
        ]);
    }

    public function update(UpdateDataSubjectRequestAccessRequest $request, DataSubjectRequestAccess $dataSubjectRequestAccess): JsonResponse
    {
        $data = $request->validated();

        if (
            isset($data['verification_status']) &&
            $data['verification_status'] !== $dataSubjectRequestAccess->verification_status &&
            $data['verification_status'] === VerificationStatus::VERIFIED->value
        ) {
            $data['verification_date'] = now();
        }

        $updatedDataSubjectRequestAccess = $this->repository->updateDataSubjectRequestAccess($dataSubjectRequestAccess, $data);

        return response()->json([
            'error' => false,
            'message' => 'Data Subject Request Access updated successfully',
            'data' => $updatedDataSubjectRequestAccess,
        ]);
    }

    public function destroy(DataSubjectRequestAccess $dataSubjectRequestAccess): JsonResponse
    {
        $this->repository->deleteDataSubjectRequestAccess($dataSubjectRequestAccess);

        return response()->json([
            'error' => false,
            'message' => 'Data Subject Request Access deleted successfully',
        ]);
    }
}

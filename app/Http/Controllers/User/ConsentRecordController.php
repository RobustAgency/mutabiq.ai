<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use App\Models\ConsentRecord;
use App\Http\Controllers\Controller;
use App\Enums\ConsentRecord\Lifecycle;
use App\Http\Resources\ConsentRecordResource;
use App\Repositories\ConsentRecordRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Requests\ConsentRecord\ListConsentRecordRequest;
use App\Http\Requests\ConsentRecord\StoreConsentRecordRequest;
use App\Http\Requests\ConsentRecord\UpdateConsentRecordRequest;

class ConsentRecordController extends Controller
{
    public function __construct(private ConsentRecordRepository $repository) {}

    public function index(ListConsentRecordRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $consentRecords = $this->repository->getFilteredConsentRecords($filters);

        return response()->json([
            'error' => false,
            'data' => $consentRecords,
            'message' => 'Consent records retrieved successfully',
        ]);
    }

    public function store(StoreConsentRecordRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($data['lifecycle_stage'] === Lifecycle::OBTAINED->value) {
            $data['obtained_date'] = now();
        } elseif ($data['lifecycle_stage'] === Lifecycle::WITHDRAWN->value) {
            $data['withdrawn_date'] = now();
        }

        $uuid = Str::uuid()->toString();
        $data['consent_code'] = 'CNY-'.date('Y').'-'.$uuid;

        $consentRecord = $this->repository->createConsentRecord($data);

        return response()->json([
            'error' => false,
            'message' => 'Consent record created successfully',
            'data' => $consentRecord,
        ], 201);
    }

    public function show(ConsentRecord $consentRecord): JsonResponse
    {
        return response()->json([
            'error' => false,
            'message' => 'Consent record retrieved successfully',
            'data' => new ConsentRecordResource($consentRecord),
        ]);
    }

    public function update(UpdateConsentRecordRequest $request, ConsentRecord $consentRecord): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['lifecycle_stage']) && $consentRecord->lifecycle_stage !== $data['lifecycle_stage']) {
            if ($data['lifecycle_stage'] === Lifecycle::OBTAINED->value) {
                $data['obtained_date'] = now();
            } elseif ($data['lifecycle_stage'] === Lifecycle::WITHDRAWN->value) {
                $data['withdrawn_date'] = now();
            }
        }

        $updatedConsentRecord = $this->repository->updateConsentRecord($consentRecord, $data);

        return response()->json([
            'error' => false,
            'message' => 'Consent record updated successfully',
            'data' => $updatedConsentRecord,
        ]);
    }

    public function destroy(ConsentRecord $consentRecord): JsonResponse
    {
        $this->repository->deleteConsentRecord($consentRecord);

        return response()->json([
            'error' => false,
            'message' => 'Consent record deleted successfully',
            'data' => null,
        ]);
    }
}

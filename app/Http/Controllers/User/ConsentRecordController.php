<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Enums\ConsentRecord\Lifecycle;
use App\Repositories\ConsentRecordRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Requests\ConsentRecord\StoreConsentRecordRequest;

class ConsentRecordController extends Controller
{
    public function __construct(private ConsentRecordRepository $repository) {}

    public function store(StoreConsentRecordRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($data['lifecycle'] === Lifecycle::OBTAINED->value) {
            $data['obtained_date'] = now();
        } elseif ($data['lifecycle'] === Lifecycle::WITHDRAWN->value) {
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
}

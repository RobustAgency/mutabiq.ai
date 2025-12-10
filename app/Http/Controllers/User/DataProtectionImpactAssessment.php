<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repositories\DataProtectionImpactAssessmentRepository;
use App\Http\Requests\DataProtectionImpactAssessment\StoreDataProtectionImpactAssessmentRequest;

class DataProtectionImpactAssessment extends Controller
{
    public function __construct(private DataProtectionImpactAssessmentRepository $repository) {}

    public function store(StoreDataProtectionImpactAssessmentRequest $request): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        $uuid = Str::uuid()->toString();
        $data['dpia_code'] = 'DPIA-'.date('Y').'-'.$uuid;

        if (isset($data['review_frequency_months'])) {
            $data['next_review_date'] = now()->addMonths($data['review_frequency_months']);
        }

        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;

        $dpia = $this->repository->createDataProtectionImpactAssessment($data);

        return response()->json([
            'error' => false,
            'message' => 'Data Protection Impact Assessment created successfully',
            'data' => $dpia,
        ], 201);
    }
}

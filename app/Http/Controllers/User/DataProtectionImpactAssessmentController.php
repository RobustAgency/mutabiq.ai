<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\DataProtectionImpactAssessment;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Resources\DataProtectionImpactAssessmentResource;
use App\Repositories\DataProtectionImpactAssessmentRepository;
use App\Http\Requests\DataProtectionImpactAssessment\ListDataProtectionImpactAssessmentRequest;
use App\Http\Requests\DataProtectionImpactAssessment\StoreDataProtectionImpactAssessmentRequest;
use App\Http\Requests\DataProtectionImpactAssessment\UpdateDataProtectionImpactAssessmentRequest;

class DataProtectionImpactAssessmentController extends Controller
{
    public function __construct(private DataProtectionImpactAssessmentRepository $repository) {}

    public function index(ListDataProtectionImpactAssessmentRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $dataProtectionImpactAssessments = $this->repository->getFilteredDataProtectionImpactAssessments($filters);

        return response()->json([
            'error' => false,
            'data' => $dataProtectionImpactAssessments,
            'message' => 'Data Protection Impact Assessments retrieved successfully',
        ]);
    }

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

        $dataProtectionImpactAssessment = $this->repository->createDataProtectionImpactAssessment($data);

        return response()->json([
            'error' => false,
            'message' => 'Data Protection Impact Assessment created successfully',
            'data' => $dataProtectionImpactAssessment,
        ], 201);
    }

    public function show(DataProtectionImpactAssessment $dataProtectionImpactAssessment): JsonResponse
    {
        return response()->json([
            'error' => false,
            'message' => 'Data Protection Impact Assessment retrieved successfully',
            'data' => new DataProtectionImpactAssessmentResource($dataProtectionImpactAssessment),
        ]);
    }

    public function update(UpdateDataProtectionImpactAssessmentRequest $request,
        DataProtectionImpactAssessment $dataProtectionImpactAssessment): JsonResponse
    {
        $user = Auth::user();
        $data = $request->validated();

        if (isset($data['review_frequency_months'])) {
            $data['next_review_date'] = now()->addMonths($data['review_frequency_months']);
        }

        $data['updated_by'] = $user->id;

        $updatedDataProtectionImpactAssessment = $this->repository->updateDataProtectionImpactAssessment($dataProtectionImpactAssessment, $data);

        return response()->json([
            'error' => false,
            'message' => 'Data Protection Impact Assessment updated successfully',
            'data' => $updatedDataProtectionImpactAssessment,
        ]);
    }

    public function destroy(DataProtectionImpactAssessment $dataProtectionImpactAssessment): JsonResponse
    {
        $this->repository->deleteDataProtectionImpactAssessment($dataProtectionImpactAssessment);

        return response()->json([
            'data' => null,
            'error' => false,
            'message' => 'Data Protection Impact Assessment deleted successfully',
        ]);
    }
}

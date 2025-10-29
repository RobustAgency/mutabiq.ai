<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncidentRootCauseAnalysis\StoreIncidentRootCauseAnalysisRequest;
use App\Http\Requests\IncidentRootCauseAnalysis\UpdateIncidentRootCauseAnalysisRequest;
use App\Http\Resources\IncidentRootCauseAnalysisResource;
use App\Models\IncidentRootCauseAnalysis;
use App\Repositories\IncidentRootCauseAnalysisRepository;
use Illuminate\Http\JsonResponse;

class IncidentRootCauseAnalysisController extends Controller
{
    public function __construct(
        protected IncidentRootCauseAnalysisRepository $incidentRootCauseAnalysisRepository
    ) {}

    /**
     * Display a listing of incident root cause analyses.
     */
    public function index(): JsonResponse
    {
        $incidentRootCauseAnalyses = $this->incidentRootCauseAnalysisRepository->getPaginatedIncidentRootCauseAnalyses();

        return response()->json([
            'error' => false,
            'message' => 'Incident root cause analyses retrieved successfully',
            'data' => $incidentRootCauseAnalyses,
        ]);
    }

    /**
     * Store a newly created incident root cause analysis.
     */
    public function store(StoreIncidentRootCauseAnalysisRequest $request): JsonResponse
    {
        $incidentRootCauseAnalysis = $this->incidentRootCauseAnalysisRepository->createIncidentRootCauseAnalysis($request->validated());

        return response()->json([
            'error' => false,
            'message' => 'Incident root cause analysis created successfully',
            'data' => new IncidentRootCauseAnalysisResource($incidentRootCauseAnalysis),
        ], 201);
    }

    /**
     * Display the specified incident root cause analysis.
     */
    public function show(IncidentRootCauseAnalysis $incidentRootCauseAnalysis): JsonResponse
    {
        $incidentRootCauseAnalysis = $this->incidentRootCauseAnalysisRepository->getIncidentRootCauseAnalysisById($incidentRootCauseAnalysis);
        return response()->json([
            'error' => false,
            'message' => 'Incident root cause analysis retrieved successfully',
            'data' => new IncidentRootCauseAnalysisResource($incidentRootCauseAnalysis),
        ]);
    }

    /**
     * Update the specified incident root cause analysis.
     */
    public function update(UpdateIncidentRootCauseAnalysisRequest $request, IncidentRootCauseAnalysis $incidentRootCauseAnalysis): JsonResponse
    {
        $updatedIncidentRootCauseAnalysis = $this->incidentRootCauseAnalysisRepository->updateIncidentRootCauseAnalysis(
            $incidentRootCauseAnalysis,
            $request->validated()
        );

        return response()->json([
            'error' => false,
            'message' => 'Incident root cause analysis updated successfully',
            'data' => new IncidentRootCauseAnalysisResource($updatedIncidentRootCauseAnalysis),
        ]);
    }

    /**
     * Remove the specified incident root cause analysis.
     */
    public function destroy(IncidentRootCauseAnalysis $incidentRootCauseAnalysis): JsonResponse
    {
        $this->incidentRootCauseAnalysisRepository->deleteIncidentRootCauseAnalysis($incidentRootCauseAnalysis);

        return response()->json([
            'error' => false,
            'message' => 'Incident root cause analysis deleted successfully',
        ]);
    }
}

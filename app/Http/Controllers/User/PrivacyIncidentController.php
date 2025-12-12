<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\PrivacyIncident;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PrivacyIncidentResource;
use App\Repositories\PrivacyIncidentRepository;
use App\Http\Requests\PrivacyIncident\ListPrivacyIncidentRequest;
use App\Http\Requests\PrivacyIncident\StorePrivacyIncidentRequest;
use App\Http\Requests\PrivacyIncident\UpdatePrivacyIncidentRequest;

class PrivacyIncidentController extends Controller
{
    public function __construct(private PrivacyIncidentRepository $repository) {}

    public function index(ListPrivacyIncidentRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $incidents = $this->repository->getFilteredPrivacyIncidents($filters);

        return response()->json([
            'error' => false,
            'message' => 'Privacy incidents retrieved successfully',
            'data' => $incidents,
        ]);
    }

    public function store(StorePrivacyIncidentRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $validated['organization_id'] = $user->organization_id;
        $uuid = Str::uuid()->toString();
        $validated['incident_code'] = 'INC-'.date('Y').'-'.$uuid;
        $validated['created_by'] = $user->id;
        $validated['updated_by'] = $user->id;

        if (isset($validated['detected_date'])) {
            $detected = Carbon::parse($validated['detected_date']);
            $validated['notification_deadline'] = $detected->copy()->addHours(72);

            $validated['days_to_resolution'] = isset($validated['resolution_date'])
                ? $detected->diffInDays(Carbon::parse($validated['resolution_date']))
                : null;
        }

        $privacyIncident = $this->repository->createPrivacyIncident($validated);

        return response()->json([
            'error' => false,
            'message' => 'Privacy incident created successfully',
            'data' => $privacyIncident,
        ], 201);
    }

    public function show(PrivacyIncident $privacyIncident): JsonResponse
    {
        return response()->json([
            'error' => false,
            'message' => 'Privacy incident retrieved successfully',
            'data' => new PrivacyIncidentResource($privacyIncident),
        ]);
    }

    public function update(UpdatePrivacyIncidentRequest $request, PrivacyIncident $privacyIncident): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();
        $validated['updated_by'] = $user->id;

        $existingDetected = $privacyIncident->detected_date
            ? Carbon::parse($privacyIncident->detected_date)
            : null;

        $newDetected = isset($validated['detected_date'])
            ? Carbon::parse($validated['detected_date'])
            : null;

        $newResolution = isset($validated['resolution_date'])
            ? Carbon::parse($validated['resolution_date'])
            : null;

        if ($newDetected && (! $existingDetected || ! $existingDetected->eq($newDetected))) {
            $validated['notification_deadline'] = $newDetected->copy()->addHours(72);
        }

        if ($newDetected && $newResolution) {
            $validated['days_to_resolution'] = $newDetected->diffInDays($newResolution);
        }

        $updatedIncident = $this->repository->updatePrivacyIncident($privacyIncident, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Privacy incident updated successfully',
            'data' => $updatedIncident,
        ]);
    }

    public function destroy(PrivacyIncident $privacyIncident): JsonResponse
    {
        $this->repository->deletePrivacyIncident($privacyIncident);

        return response()->json([
            'error' => false,
            'message' => 'Privacy incident deleted successfully',
            'data' => null,
        ]);
    }
}

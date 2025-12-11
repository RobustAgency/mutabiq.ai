<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\PrivacyIncident;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\PrivacyIncidentRepository;
use App\Http\Requests\PrivacyIncident\StorePrivacyIncidentRequest;
use App\Http\Requests\PrivacyIncident\UpdatePrivacyIncidentRequest;

class PrivacyIncidentController extends Controller
{
    public function __construct(private PrivacyIncidentRepository $repository) {}

    public function store(StorePrivacyIncidentRequest $request): JsonResponse
    {
        $validated = $request->validated();

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

    public function update(UpdatePrivacyIncidentRequest $request, PrivacyIncident $privacyIncident): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['detected_date']) && $privacyIncident->detected_date !== $validated['detected_date']) {
            $detected = Carbon::parse($validated['detected_date']);
            $validated['notification_deadline'] = $detected->copy()->addHours(72);

            $validated['days_to_resolution'] = isset($validated['resolution_date'])
                ? $detected->diffInDays(Carbon::parse($validated['resolution_date']))
                : null;
        }

        $updatedIncident = $this->repository->updatePrivacyIncident($privacyIncident, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Privacy incident updated successfully',
            'data' => $updatedIncident,
        ]);
    }
}

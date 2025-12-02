<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Control;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ControlResource;
use App\Repositories\ControlRepository;
use App\Http\Requests\StoreControlRequest;
use App\Http\Requests\SearchControlRequest;
use App\Http\Requests\UpdateControlRequest;

class ControlController extends Controller
{
    public function __construct(protected ControlRepository $controlRepository) {}

    public function index(SearchControlRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validated();
        $controls = $this->controlRepository->getFilteredControls($user, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Controls retrieved successfully',
            'data' => $controls,
        ]);
    }

    public function store(StoreControlRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validated();
        $this->controlRepository->createForAdmin($user, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Control created successfully',
            'data' => null,
        ], 201);
    }

    public function show(Control $control): JsonResponse
    {
        $control->load('requirements');

        return response()->json([
            'error' => false,
            'message' => 'Control retrieved successfully',
            'data' => new ControlResource($control),
        ]);
    }

    public function update(UpdateControlRequest $request, Control $control): JsonResponse
    {
        $validated = $request->validated();
        $control = $this->controlRepository->update($control, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Control updated successfully',
            'data' => null,
        ]);
    }
}

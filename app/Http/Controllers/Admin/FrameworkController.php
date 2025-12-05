<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Framework;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FrameworkResource;
use App\Repositories\FrameworkRepository;
use App\Http\Requests\StoreFrameworkRequest;
use App\Http\Requests\SearchFrameworkRequest;
use App\Http\Requests\UpdateFrameworkRequest;

class FrameworkController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private FrameworkRepository $frameworkRepository
    ) {}

    public function index(SearchFrameworkRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validated();
        $frameworks = $this->frameworkRepository->getFilteredFrameworks($user, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Frameworks retrieved successfully',
            'data' => $frameworks,
        ]);
    }

    public function store(StoreFrameworkRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validated();

        $this->frameworkRepository->createForAdmin($user, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Framework created successfully',
            'data' => null,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $framework = $this->frameworkRepository->getFrameworkByID($id);

        return response()->json([
            'error' => false,
            'message' => 'Framework retrieved successfully',
            'data' => new FrameworkResource($framework),
        ]);
    }

    public function update(UpdateFrameworkRequest $request, Framework $framework): JsonResponse
    {
        $validated = $request->validated();

        $framework = $this->frameworkRepository->update($framework, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Framework updated successfully',
            'data' => null,
        ]);
    }
}

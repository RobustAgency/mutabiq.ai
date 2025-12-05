<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\FrameworkRepository;
use App\Http\Requests\SearchFrameworkRequest;

class FrameworkController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private FrameworkRepository $frameworkRepository) {}

    public function index(SearchFrameworkRequest $request): JsonResponse
    {
        $frameworks = $this->frameworkRepository->getPublishedFrameworks($request->validated());

        return response()->json([
            'error' => false,
            'message' => 'Frameworks retrieved successfully',
            'data' => $frameworks,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $framework = $this->frameworkRepository->getFrameworkByID($id);

        return response()->json([
            'error' => false,
            'message' => 'Framework retrieved successfully',
            'data' => $framework,
        ]);
    }
}

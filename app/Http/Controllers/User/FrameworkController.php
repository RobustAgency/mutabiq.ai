<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Repositories\FrameworkRepository;
use App\Http\Requests\FrameworkRequest;
use App\Models\Framework;

class FrameworkController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private FrameworkRepository $frameworkRepository) {}

    public function index(FrameworkRequest $request)
    {
        $frameworks = $this->frameworkRepository->getPublishedFrameworks($request->validated());

        return response()->json([
            'error' => false,
            'message' => 'Frameworks retrieved successfully',
            'data' => $frameworks,
        ]);
    }

    public function show(Framework $framework)
    {
        $framework = $this->frameworkRepository->getFrameworkByID($framework->id);

        return response()->json([
            'error' => false,
            'message' => 'Framework retrieved successfully',
            'data' => $framework,
        ]);
    }
}
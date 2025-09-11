<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Repositories\FrameworkRepository;
use App\Http\Requests\FrameworkRequest;

class FrameworkController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private FrameworkRepository $frameworkRepository) {}

    public function index(FrameworkRequest $request)
    {
        $frameworks = $this->frameworkRepository->getAvailableFrameworks($request->validated());

        return response()->json([
            'error' => false,
            'message' => 'Frameworks retrieved successfully',
            'data' => $frameworks,
        ]);
    }
}
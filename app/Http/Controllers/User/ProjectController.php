<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddFrameworksToProject;
use App\Http\Requests\AddMemberToProjectRequest;
use App\Http\Requests\SearchProjectsRequest;
use App\Repositories\ProjectRepository;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function __construct(private ProjectRepository $projectRepository) {}

    public function index(SearchProjectsRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $projects = $this->projectRepository->getFilteredProjects($user->id, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Projects retrieved successfully',
            'data' => $projects,
        ]);
    }

    public function show(Project $project): JsonResponse
    {
        $projectData = $this->projectRepository->getProjectByID($project);
        return response()->json([
            'error' => false,
            'message' => 'Project retrieved successfully',
            'data' => new ProjectResource($projectData),
        ]);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $this->projectRepository->createProject($user, $validated);

        return response()->json([
            'data' => null,
            'error' => false,
            'message' => 'Project created successfully',
        ], 201);
    }

    public function addMember(AddMemberToProjectRequest $request, Project $project): JsonResponse
    {
        $validated = $request->validated();

        $this->projectRepository->addMemberToProject($project, $validated);

        return response()->json([
            'data' => null,
            'error' => false,
            'message' => 'Member added to project successfully',
        ]);
    }

    public function addFrameworks(AddFrameworksToProject $request, Project $project): JsonResponse
    {
        $validated = $request->validated();

        $this->projectRepository->addFrameworksToProject($project, $validated['framework_ids']);

        return response()->json([
            'data' => null,
            'error' => false,
            'message' => 'Frameworks added to project successfully',
        ]);
    }
}

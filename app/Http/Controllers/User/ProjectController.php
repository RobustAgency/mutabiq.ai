<?php

namespace App\Http\Controllers\User;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ProjectResource;
use App\Repositories\ProjectRepository;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\AddFrameworkToProject;
use App\Http\Requests\SearchProjectsRequest;
use App\Http\Requests\AddMemberToProjectRequest;

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
        $validated['organization_id'] = $user->organization_id;

        $project = $this->projectRepository->createProject($user, $validated);

        return response()->json([
            'data' => new ProjectResource($project),
            'error' => false,
            'message' => 'Project created successfully',
        ], 201);
    }

    public function update(Project $project, StoreProjectRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $project = $this->projectRepository->updateProject($project, $validated);

        return response()->json([
            'data' => new ProjectResource($project),
            'error' => false,
            'message' => 'Project updated successfully',
        ]);
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

    public function addFramework(AddFrameworkToProject $request, Project $project): JsonResponse
    {
        $validated = $request->validated();

        $this->projectRepository->addFrameworkToProject($project, $validated['framework_id']);

        return response()->json([
            'data' => null,
            'error' => false,
            'message' => 'Framework added to project successfully',
        ]);
    }
}

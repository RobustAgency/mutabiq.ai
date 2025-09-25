<?php

namespace App\Repositories;

use App\Enums\UserProjectRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectRepository
{
    /**
     * Get filtered projects for a user with optional filters.
     *
     * @param int $userID
     * @param array $filters
     * @return LengthAwarePaginator<int, Project>
     */
    public function getFilteredProjects(int $userID, array $filters = []): LengthAwarePaginator
    {
        $query = Project::whereHas('users', function ($q) use ($userID) {
            $q->where('user_id', $userID);
        })->with('users', 'frameworks')->withCount('users', 'frameworks');

        $query->when(! empty($filters['name']), function ($query) use ($filters) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        });

        $query->when(! empty($filters['governance_pillar']), function ($query) use ($filters) {
            $query->where('governance_pillar', $filters['governance_pillar']);
        });

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    public function getProjectByID(int $projectID): array
    {
        $project = Project::with(['users', 'frameworks.requirements', 'frameworks.controls'])
            ->findOrFail($projectID);

        $totalRequirements = $project->frameworks->sum(fn($fw) => $fw->requirements->count());
        $totalControls = $project->frameworks->sum(fn($fw) => $fw->controls->count());

        return [
            'project' => $project,
            'total_requirements' => $totalRequirements,
            'total_controls' => $totalControls,
        ];
    }


    public function createProject(User $user, array $projectData): Project
    {
        $project = Project::create($projectData);
        $project->users()->attach($user->id, ['role' => UserProjectRole::OWNER]);

        return $project;
    }

    public function addMemberToProject(Project $project, array $data): Project
    {
        $project->users()->attach($data['user_id'], ['role' => $data['role']]);

        return $project;
    }

    public function addFrameworksToProject(Project $project, array $frameworkIDs): Project
    {
        $project->frameworks()->syncWithoutDetaching($frameworkIDs);

        return $project;
    }
}

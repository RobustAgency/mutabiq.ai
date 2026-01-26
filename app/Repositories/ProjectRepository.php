<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Project;
use App\Enums\UserProjectRole;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectRepository
{
    /**
     * Get filtered projects for a user with optional filters.
     *
     * @return LengthAwarePaginator<int, Project>
     */
    public function getFilteredProjects(int $userID, array $filters = []): LengthAwarePaginator
    {
        $query = Project::whereHas('users', function ($q) use ($userID) {
            $q->where('user_id', $userID);
        })->with('users', 'framework');

        $query->when(! empty($filters['name']), function ($query) use ($filters) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        });

        $query->when(! empty($filters['governance_pillar']), function ($query) use ($filters) {
            $query->where('governance_pillar', $filters['governance_pillar']);
        });

        $perPage = $filters['per_page'] ?? 10;

        return $query->latest()->paginate($perPage);
    }

    public function getProjectByID(Project $project): Project
    {
        $project->load([
            'users',
            'framework' => function ($query) {
                $query->withCount('requirements')
                    ->with(['requirements' => function ($reqQuery) {
                        $reqQuery->with('controls')->withCount('controls');
                    }]);
            },
        ]);

        return $project;
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

    public function addFrameworkToProject(Project $project, int $frameworkID): Project
    {
        $project->update([
            'framework_id' => $frameworkID,
        ]);

        return $project;
    }

    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh();
    }
}

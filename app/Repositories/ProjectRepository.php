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
        })->with('users', 'framework')->withCount('users', 'framework');

        $query->when(! empty($filters['name']), function ($query) use ($filters) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
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
                $query->withCount(['requirements', 'controls']);
            }
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
        $project->framework_id = $frameworkID;
        $project->save();

        return $project;
    }
}

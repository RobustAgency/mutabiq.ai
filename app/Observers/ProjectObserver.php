<?php

namespace App\Observers;

use App\Models\Project;

class ProjectObserver extends ActivityAwareObserver
{
    protected function getTrackedFields(): array
    {
        return [
            'ai_model_id',
            'name',
            'description',
            'governance_pillar',
            'progress',
            'framework_id',
        ];
    }

    public function created(Project $project): void
    {
        $this->logCreate($project);
    }

    public function updating(Project $project): void
    {
        $this->logUpdate($project, $project->getOriginal());
    }

    public function deleted(Project $project): void
    {
        $this->logDelete($project);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'core-assets' => [
                'ai-models' => ['view', 'create', 'edit', 'delete', 'approve'],
                'ai-model-versions' => ['view', 'create', 'edit', 'delete', 'approve'],
                'ai-model-cards' => ['view', 'create', 'edit', 'delete', 'approve'],
                'use-cases' => ['view', 'create', 'edit', 'delete', 'approve'],
                'ai-model-use-cases' => ['view', 'create', 'edit', 'delete', 'approve'],
                'ai-assets' => ['view', 'create', 'edit', 'delete', 'approve'],
                'ai-model-artifacts' => ['view', 'create', 'edit', 'delete', 'approve'],
                'artifact-access-logs' => ['view', 'create', 'delete', 'approve'],
                'stakeholders' => ['view', 'create', 'edit', 'delete', 'approve'],
                'vendors' => ['view', 'create', 'edit', 'delete', 'approve'],
                'agreements' => ['view', 'create', 'edit', 'delete', 'approve'],
                'datasets' => ['view', 'create', 'edit', 'delete', 'approve'],
                'data-sources' => ['view', 'create', 'edit', 'delete', 'approve'],
                'data-elements' => ['view', 'create', 'edit', 'delete', 'approve'],
                'dataset-snapshots' => ['view', 'create', 'edit', 'delete', 'approve'],
                'ai-model-datasets' => ['view', 'create', 'edit', 'approve'],
                'dataset-subject-populations' => ['view', 'create', 'edit', 'delete', 'approve'],
            ],
            'risk-management-and-compliance' => [
                'ai-risk-register' => ['view', 'create', 'edit', 'delete', 'approve'],
                'risk-methodologies' => ['view', 'create', 'edit', 'delete', 'approve'],
                'ai-risk-treatments' => ['view', 'create', 'edit', 'delete', 'approve'],
                'kri-indicators' => ['view', 'create', 'edit', 'delete', 'approve'],
                'projects' => ['view', 'create', 'edit', 'approve'],
                'frameworks' => ['view', 'approve'],
                'compliance-evidences' => ['view', 'create', 'edit', 'delete', 'approve'],
                'regulatory-submissions' => ['view', 'create', 'edit', 'delete', 'approve'],
            ],
            'privacy-and-data-protection' => [
                'record-of-processing-activities' => ['view', 'create', 'edit', 'delete', 'approve'],
                'user-consents' => ['view', 'create', 'edit', 'delete', 'approve'],
                'consent-scopes' => ['view', 'create', 'edit', 'delete', 'approve'],
                'consent-coverages' => ['view', 'create', 'edit', 'delete', 'approve'],
                'consent-records' => ['view', 'create', 'edit', 'delete', 'approve'],
                'data-subject-request-accesses' => ['view', 'create', 'edit', 'delete', 'approve'],
                'data-protection-impact-assessments' => ['view', 'create', 'edit', 'delete', 'approve'],
                'privacy-incidents' => ['view', 'create', 'edit', 'delete', 'approve'],
                'pdp-processing-registers' => ['view', 'create', 'edit', 'delete', 'approve'],
            ],
            'governance-and-oversight' => [
                'ai-committees' => ['view', 'create', 'edit', 'delete', 'approve'],
                'committee-memberships' => ['view', 'create', 'edit', 'delete', 'approve'],
                'committee-meetings' => ['view', 'create', 'edit', 'delete', 'approve'],
                'committee-decisions' => ['view', 'create', 'edit', 'delete', 'approve'],
                'committee-actions' => ['view', 'create', 'edit', 'delete', 'approve'],
                'ai-incidents' => ['view', 'create', 'edit', 'delete', 'approve'],
                'incident-alerts' => ['view', 'create', 'edit', 'delete', 'approve'],
                'incident-actions' => ['view', 'create', 'edit', 'delete', 'approve'],
                'incident-root-cause-analyses' => ['view', 'create', 'edit', 'delete', 'approve'],
                'incident-notifications' => ['view', 'create', 'edit', 'delete', 'approve'],
                'corrective-preventive-actions' => ['view', 'create', 'edit', 'delete', 'approve'],

            ],
        ];

        foreach ($permissions as $category => $resources) {
            foreach ($resources as $resource => $actions) {
                foreach ($actions as $action) {
                    Permission::firstOrCreate([
                        'name' => "{$category}.{$resource}.{$action}",
                        'guard_name' => 'supabase',
                    ], [
                        'guard_name' => 'supabase',
                    ]);
                }
            }
        }
    }
}

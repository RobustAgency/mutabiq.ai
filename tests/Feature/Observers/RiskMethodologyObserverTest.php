<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\RiskMethodology;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RiskMethodologyObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_risk_methodology_create(): void
    {
        $methodology = RiskMethodology::factory()->create();

        $log = ActivityLog::where('actable_id', $methodology->id)
            ->where('actable_type', RiskMethodology::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('RiskMethodology created', $log->description);
    }

    public function test_logs_activity_on_risk_methodology_update(): void
    {
        $methodology = RiskMethodology::factory()->create(['name' => 'Original']);

        ActivityLog::truncate();

        $methodology->update(['name' => 'Updated']);

        $log = ActivityLog::where('actable_id', $methodology->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('RiskMethodology updated', $log->description);
        $this->assertArrayHasKey('name', $log->changes);
        $this->assertEquals('Original', $log->changes['name']['from']);
        $this->assertEquals('Updated', $log->changes['name']['to']);
    }

    public function test_logs_activity_on_risk_methodology_delete(): void
    {
        $methodology = RiskMethodology::factory()->create();
        $methodologyId = $methodology->id;

        $methodology->delete();

        $log = ActivityLog::where('actable_id', $methodologyId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('RiskMethodology deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $methodology = RiskMethodology::factory()->create([
            'name' => 'Original Methodology',
            'likelihood_scale' => 'ORIGINAL_SCALE',
            'impact_scale' => 'ORIGINAL_IMPACT',
            'owner_team' => 'Risk Team',
        ]);

        ActivityLog::truncate();

        $methodology->update([
            'name' => 'Updated Methodology',
            'likelihood_scale' => 'UPDATED_SCALE',
            'impact_scale' => 'UPDATED_IMPACT',
            'owner_team' => 'Compliance Team',
        ]);

        $log = ActivityLog::where('actable_id', $methodology->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('name', $log->changes);
        $this->assertArrayHasKey('likelihood_scale', $log->changes);
        $this->assertArrayHasKey('impact_scale', $log->changes);
        $this->assertArrayHasKey('owner_team', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $methodology = RiskMethodology::factory()->create();

        $log = ActivityLog::where('actable_id', $methodology->id)
            ->where('actable_type', RiskMethodology::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($methodology->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $methodology = RiskMethodology::factory()->create();

        $log = ActivityLog::where('actable_id', $methodology->id)
            ->where('actable_type', RiskMethodology::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_matrix_rule_array_changes(): void
    {
        $methodology = RiskMethodology::factory()->create([
            'matrix_rule' => ['rule1' => 'condition1'],
        ]);

        ActivityLog::truncate();

        $methodology->update([
            'matrix_rule' => ['rule1' => 'condition1', 'rule2' => 'condition2'],
        ]);

        $log = ActivityLog::where('actable_id', $methodology->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('matrix_rule', $log->changes);
    }

    public function test_tracks_date_range_and_policy_changes(): void
    {
        $methodology = RiskMethodology::factory()->create([
            'effective_from' => now()->subMonths(6)->format('Y-m-d'),
            'effective_to' => now()->addMonths(6)->format('Y-m-d'),
            'review_policy' => 'ANNUAL',
        ]);

        ActivityLog::truncate();

        $methodology->update([
            'effective_to' => now()->addMonths(12)->format('Y-m-d'),
            'review_policy' => 'BIENNIAL',
        ]);

        $log = ActivityLog::where('actable_id', $methodology->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('effective_to', $log->changes);
        $this->assertArrayHasKey('review_policy', $log->changes);
    }

    public function test_tracks_aggregation_and_acceptance_threshold_changes(): void
    {
        $methodology = RiskMethodology::factory()->create([
            'aggregation_logic' => 'WEIGHTED_AVERAGE',
            'acceptance_thresholds' => 'HIGH',
        ]);

        ActivityLog::truncate();

        $methodology->update([
            'aggregation_logic' => 'MAXIMUM',
            'acceptance_thresholds' => 'MEDIUM',
        ]);

        $log = ActivityLog::where('actable_id', $methodology->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('aggregation_logic', $log->changes);
        $this->assertArrayHasKey('acceptance_thresholds', $log->changes);
    }
}

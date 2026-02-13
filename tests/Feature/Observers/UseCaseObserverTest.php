<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\UseCase;
use App\Models\ActivityLog;
use App\Models\Stakeholder;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UseCaseObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_use_case_create(): void
    {
        $useCase = UseCase::factory()->create();

        $log = ActivityLog::where('actable_id', $useCase->id)
            ->where('actable_type', UseCase::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('UseCase created', $log->description);
    }

    public function test_logs_activity_on_use_case_update(): void
    {
        $useCase = UseCase::factory()->create(['status' => 'DRAFT']);

        ActivityLog::truncate();

        $useCase->update(['status' => 'APPROVED']);

        $log = ActivityLog::where('actable_id', $useCase->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('UseCase updated', $log->description);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('DRAFT', $log->changes['status']['from']);
        $this->assertEquals('APPROVED', $log->changes['status']['to']);
    }

    public function test_logs_activity_on_use_case_delete(): void
    {
        $useCase = UseCase::factory()->create();
        $useCaseId = $useCase->id;

        $useCase->delete();

        $log = ActivityLog::where('actable_id', $useCaseId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('UseCase deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $useCase = UseCase::factory()->create([
            'name' => 'Original Name',
            'status' => 'DRAFT',
            'priority' => 'HIGH',
            'preliminary_risk_level' => 'MEDIUM',
        ]);

        ActivityLog::truncate();

        $useCase->update([
            'name' => 'Updated Name',
            'status' => 'APPROVED',
            'priority' => 'CRITICAL',
            'preliminary_risk_level' => 'HIGH',
        ]);

        $log = ActivityLog::where('actable_id', $useCase->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('name', $log->changes);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertArrayHasKey('priority', $log->changes);
        $this->assertArrayHasKey('preliminary_risk_level', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $useCase = UseCase::factory()->create();

        $log = ActivityLog::where('actable_id', $useCase->id)
            ->where('actable_type', UseCase::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($useCase->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $useCase = UseCase::factory()->create();

        $log = ActivityLog::where('actable_id', $useCase->id)
            ->where('actable_type', UseCase::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_roi_and_savings_changes(): void
    {
        $useCase = UseCase::factory()->create([
            'expected_roi' => 100000,
            'estimated_time_savings' => 500,
            'estimated_cost_savings' => 50000,
        ]);

        ActivityLog::truncate();

        $useCase->update([
            'expected_roi' => 200000,
            'estimated_time_savings' => 1000,
            'estimated_cost_savings' => 100000,
        ]);

        $log = ActivityLog::where('actable_id', $useCase->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('expected_roi', $log->changes);
        $this->assertArrayHasKey('estimated_time_savings', $log->changes);
        $this->assertArrayHasKey('estimated_cost_savings', $log->changes);
    }

    public function test_tracks_risk_and_regulatory_changes(): void
    {
        $useCase = UseCase::factory()->create([
            'preliminary_risk_level' => 'LOW',
            'regulatory_impact' => 'NONE',
            'data_sensitivity' => 'PUBLIC',
        ]);

        ActivityLog::truncate();

        $useCase->update([
            'preliminary_risk_level' => 'HIGH',
            'regulatory_impact' => 'HIGH',
            'data_sensitivity' => 'CONFIDENTIAL',
        ]);

        $log = ActivityLog::where('actable_id', $useCase->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('preliminary_risk_level', $log->changes);
        $this->assertArrayHasKey('regulatory_impact', $log->changes);
        $this->assertArrayHasKey('data_sensitivity', $log->changes);
    }

    public function test_tracks_ownership_and_deployment_changes(): void
    {
        $owner1 = Stakeholder::factory()->create();
        $owner2 = Stakeholder::factory()->create();
        $techOwner1 = Stakeholder::factory()->create();
        $techOwner2 = Stakeholder::factory()->create();

        $useCase = UseCase::factory()->create([
            'business_owner_id' => $owner1->id,
            'technical_owner_id' => $techOwner1->id,
            'target_deployment_date' => now()->addMonths(3)->format('Y-m-d'),
        ]);

        ActivityLog::truncate();

        $useCase->update([
            'business_owner_id' => $owner2->id,
            'technical_owner_id' => $techOwner2->id,
            'target_deployment_date' => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $log = ActivityLog::where('actable_id', $useCase->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('business_owner_id', $log->changes);
        $this->assertArrayHasKey('technical_owner_id', $log->changes);
        $this->assertArrayHasKey('target_deployment_date', $log->changes);
    }
}

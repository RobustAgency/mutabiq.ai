<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\Dataset;
use App\Models\ActivityLog;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatasetObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_dataset_create(): void
    {
        $dataset = Dataset::factory()->create();

        $log = ActivityLog::where('actable_id', $dataset->id)
            ->where('actable_type', Dataset::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('Dataset created', $log->description);
    }

    public function test_logs_activity_on_dataset_update(): void
    {
        $dataset = Dataset::factory()->create(['status' => 'DRAFT']);

        ActivityLog::truncate();

        $dataset->update(['status' => 'ACTIVE']);

        $log = ActivityLog::where('actable_id', $dataset->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('Dataset updated', $log->description);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('DRAFT', $log->changes['status']['from']);
        $this->assertEquals('ACTIVE', $log->changes['status']['to']);
    }

    public function test_logs_activity_on_dataset_delete(): void
    {
        $dataset = Dataset::factory()->create();
        $datasetId = $dataset->id;

        $dataset->delete();

        $log = ActivityLog::where('actable_id', $datasetId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('Dataset deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $dataset = Dataset::factory()->create([
            'name' => 'Original Name',
            'sensitivity' => 'LOW',
            'status' => 'DRAFT',
            'owner_team' => 'IT',
        ]);

        ActivityLog::truncate();

        $dataset->update([
            'name' => 'Updated Name',
            'sensitivity' => 'HIGH',
            'status' => 'ACTIVE',
            'owner_team' => 'LEGAL',
        ]);

        $log = ActivityLog::where('actable_id', $dataset->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('name', $log->changes);
        $this->assertArrayHasKey('sensitivity', $log->changes);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertArrayHasKey('owner_team', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $dataset = Dataset::factory()->create();

        $log = ActivityLog::where('actable_id', $dataset->id)
            ->where('actable_type', Dataset::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($dataset->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $dataset = Dataset::factory()->create();

        $log = ActivityLog::where('actable_id', $dataset->id)
            ->where('actable_type', Dataset::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_estimated_size_changes(): void
    {
        $dataset = Dataset::factory()->create([
            'estimated_size' => 1000,
            'size_unit' => 'MB',
        ]);

        ActivityLog::truncate();

        $dataset->update([
            'estimated_size' => 5000,
            'size_unit' => 'GB',
        ]);

        $log = ActivityLog::where('actable_id', $dataset->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('estimated_size', $log->changes);
        $this->assertArrayHasKey('size_unit', $log->changes);
    }
}

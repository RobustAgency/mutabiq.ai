<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\DatasetSnapshot;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatasetSnapshotObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_dataset_snapshot_create(): void
    {
        $snapshot = DatasetSnapshot::factory()->create();

        $log = ActivityLog::where('actable_id', $snapshot->id)
            ->where('actable_type', DatasetSnapshot::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('DatasetSnapshot created', $log->description);
    }

    public function test_logs_activity_on_dataset_snapshot_update(): void
    {
        $snapshot = DatasetSnapshot::factory()->create(['status' => 'DRAFT']);

        ActivityLog::truncate();

        $snapshot->update(['status' => 'APPROVED']);

        $log = ActivityLog::where('actable_id', $snapshot->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('DatasetSnapshot updated', $log->description);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('DRAFT', $log->changes['status']['from']);
        $this->assertEquals('APPROVED', $log->changes['status']['to']);
    }

    public function test_logs_activity_on_dataset_snapshot_delete(): void
    {
        $snapshot = DatasetSnapshot::factory()->create();
        $snapshotId = $snapshot->id;

        $snapshot->delete();

        $log = ActivityLog::where('actable_id', $snapshotId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('DatasetSnapshot deleted', $log->description);
    }

    public function test_tracks_version_and_description_changes(): void
    {
        $snapshot = DatasetSnapshot::factory()->create([
            'version_tag' => 'v1.0',
            'description' => 'Original description',
        ]);

        ActivityLog::truncate();

        $snapshot->update([
            'version_tag' => 'v1.1',
            'description' => 'Updated description',
        ]);

        $log = ActivityLog::where('actable_id', $snapshot->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('version_tag', $log->changes);
        $this->assertArrayHasKey('description', $log->changes);
        $this->assertEquals('v1.0', $log->changes['version_tag']['from']);
        $this->assertEquals('v1.1', $log->changes['version_tag']['to']);
    }

    public function test_tracks_row_count_and_size_changes(): void
    {
        $snapshot = DatasetSnapshot::factory()->create([
            'row_count' => 1000,
            'total_size' => 512,
        ]);

        ActivityLog::truncate();

        $snapshot->update([
            'row_count' => 2000,
            'total_size' => 1024,
        ]);

        $log = ActivityLog::where('actable_id', $snapshot->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('row_count', $log->changes);
        $this->assertArrayHasKey('total_size', $log->changes);
        $this->assertEquals(1000, $log->changes['row_count']['from']);
        $this->assertEquals(2000, $log->changes['row_count']['to']);
    }

    public function test_tracks_encryption_and_compression_changes(): void
    {
        $snapshot = DatasetSnapshot::factory()->create([
            'encryption_status' => 'UNENCRYPTED',
            'compression' => 'NONE',
        ]);

        ActivityLog::truncate();

        $snapshot->update([
            'encryption_status' => 'ENCRYPTED',
            'compression' => 'GZIP',
        ]);

        $log = ActivityLog::where('actable_id', $snapshot->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('encryption_status', $log->changes);
        $this->assertArrayHasKey('compression', $log->changes);
    }

    public function test_tracks_approval_status_changes(): void
    {
        $snapshot = DatasetSnapshot::factory()->create([
            'status' => 'PENDING_APPROVAL',
            'approved_by' => null,
        ]);

        ActivityLog::truncate();

        $snapshot->update([
            'status' => 'APPROVED',
            'approved_by' => 123,
        ]);

        $log = ActivityLog::where('actable_id', $snapshot->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertArrayHasKey('approved_by', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $snapshot = DatasetSnapshot::factory()->create();

        $log = ActivityLog::where('actable_id', $snapshot->id)
            ->where('actable_type', DatasetSnapshot::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($snapshot->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $snapshot = DatasetSnapshot::factory()->create();

        $log = ActivityLog::where('actable_id', $snapshot->id)
            ->where('actable_type', DatasetSnapshot::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_storage_details_changes(): void
    {
        $snapshot = DatasetSnapshot::factory()->create([
            'storage_uri' => 's3://bucket/original',
            'storage_tier' => 'STANDARD',
        ]);

        ActivityLog::truncate();

        $snapshot->update([
            'storage_uri' => 's3://bucket/updated',
            'storage_tier' => 'GLACIER',
        ]);

        $log = ActivityLog::where('actable_id', $snapshot->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('storage_uri', $log->changes);
        $this->assertArrayHasKey('storage_tier', $log->changes);
    }
}

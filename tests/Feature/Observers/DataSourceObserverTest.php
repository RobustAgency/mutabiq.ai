<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\DataSource;
use App\Models\ActivityLog;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataSourceObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_data_source_create(): void
    {
        $dataSource = DataSource::factory()->create();

        $log = ActivityLog::where('actable_id', $dataSource->id)
            ->where('actable_type', DataSource::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('DataSource created', $log->description);
    }

    public function test_logs_activity_on_data_source_update(): void
    {
        $dataSource = DataSource::factory()->create(['status' => 'ACTIVE']);

        ActivityLog::truncate();

        $dataSource->update(['status' => 'DECOMMISSIONED']);

        $log = ActivityLog::where('actable_id', $dataSource->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('DataSource updated', $log->description);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('ACTIVE', $log->changes['status']['from']);
        $this->assertEquals('DECOMMISSIONED', $log->changes['status']['to']);
    }

    public function test_logs_activity_on_data_source_delete(): void
    {
        $dataSource = DataSource::factory()->create();
        $dataSourceId = $dataSource->id;

        $dataSource->delete();

        $log = ActivityLog::where('actable_id', $dataSourceId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('DataSource deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $dataSource = DataSource::factory()->create([
            'name' => 'Original Name',
            'system_type' => 'DATABASE',
            'criticality_level' => 'MEDIUM',
            'status' => 'ACTIVE',
        ]);

        ActivityLog::truncate();

        $dataSource->update([
            'name' => 'Updated Name',
            'system_type' => 'API',
            'criticality_level' => 'HIGH',
            'status' => 'MAINTENANCE',
        ]);

        $log = ActivityLog::where('actable_id', $dataSource->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('name', $log->changes);
        $this->assertArrayHasKey('system_type', $log->changes);
        $this->assertArrayHasKey('criticality_level', $log->changes);
        $this->assertArrayHasKey('status', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $dataSource = DataSource::factory()->create();

        $log = ActivityLog::where('actable_id', $dataSource->id)
            ->where('actable_type', DataSource::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($dataSource->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $dataSource = DataSource::factory()->create();

        $log = ActivityLog::where('actable_id', $dataSource->id)
            ->where('actable_type', DataSource::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_ownership_changes(): void
    {
        $dataSource = DataSource::factory()->create([
            'technical_owner' => 'owner1@example.com',
            'business_owner' => 'business1@example.com',
        ]);

        ActivityLog::truncate();

        $dataSource->update([
            'technical_owner' => 'owner2@example.com',
            'business_owner' => 'business2@example.com',
        ]);

        $log = ActivityLog::where('actable_id', $dataSource->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('technical_owner', $log->changes);
        $this->assertArrayHasKey('business_owner', $log->changes);
    }

    public function test_tracks_review_date_changes(): void
    {
        $dataSource = DataSource::factory()->create([
            'last_review_date' => now()->subMonths(3)->format('Y-m-d'),
            'next_review_date' => now()->addMonths(3)->format('Y-m-d'),
        ]);

        ActivityLog::truncate();

        $dataSource->update([
            'last_review_date' => now()->format('Y-m-d'),
            'next_review_date' => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $log = ActivityLog::where('actable_id', $dataSource->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('last_review_date', $log->changes);
        $this->assertArrayHasKey('next_review_date', $log->changes);
    }

    public function test_tracks_data_domains_array_changes(): void
    {
        $dataSource = DataSource::factory()->create(['data_domains' => ['CUSTOMER']]);

        ActivityLog::truncate();

        $dataSource->update(['data_domains' => ['CUSTOMER', 'PRODUCT']]);

        $log = ActivityLog::where('actable_id', $dataSource->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('data_domains', $log->changes);
        $this->assertEquals(['CUSTOMER'], $log->changes['data_domains']['from']);
        $this->assertEquals(['CUSTOMER', 'PRODUCT'], $log->changes['data_domains']['to']);
    }
}

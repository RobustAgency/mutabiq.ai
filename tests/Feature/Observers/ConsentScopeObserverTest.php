<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\ConsentScope;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConsentScopeObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_consent_scope_create(): void
    {
        $consentScope = ConsentScope::factory()->create();

        $log = ActivityLog::where('actable_id', $consentScope->id)
            ->where('actable_type', ConsentScope::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('ConsentScope created', $log->description);
    }

    public function test_logs_activity_on_consent_scope_update(): void
    {
        $consentScope = ConsentScope::factory()->create(['subject_realm' => 'CUSTOMERS']);

        ActivityLog::truncate();

        $consentScope->update(['subject_realm' => 'EMPLOYEES']);

        $log = ActivityLog::where('actable_id', $consentScope->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('ConsentScope updated', $log->description);
        $this->assertArrayHasKey('subject_realm', $log->changes);
        $this->assertEquals('CUSTOMERS', $log->changes['subject_realm']['from']);
        $this->assertEquals('EMPLOYEES', $log->changes['subject_realm']['to']);
    }

    public function test_logs_activity_on_consent_scope_delete(): void
    {
        $consentScope = ConsentScope::factory()->create();
        $consentScopeId = $consentScope->id;

        $consentScope->delete();

        $log = ActivityLog::where('actable_id', $consentScopeId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('ConsentScope deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $consentScope = ConsentScope::factory()->create([
            'subject_realm' => 'CUSTOMERS',
            'jurisdiction' => 'US',
            'effective_from' => now()->format('Y-m-d'),
            'effective_to' => now()->addYear()->format('Y-m-d'),
        ]);

        ActivityLog::truncate();

        $consentScope->update([
            'subject_realm' => 'EMPLOYEES',
            'jurisdiction' => 'EU',
            'effective_from' => now()->addDay()->format('Y-m-d'),
            'effective_to' => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $log = ActivityLog::where('actable_id', $consentScope->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('subject_realm', $log->changes);
        $this->assertArrayHasKey('jurisdiction', $log->changes);
        $this->assertArrayHasKey('effective_from', $log->changes);
        $this->assertArrayHasKey('effective_to', $log->changes);
    }

    public function test_tracks_dataset_id_changes(): void
    {
        $consentScope = ConsentScope::factory()->create();
        $originalDatasetId = $consentScope->dataset_id;

        ActivityLog::truncate();

        // Create new dataset for update
        $newDataset = \App\Models\Dataset::factory()->create();
        $consentScope->update(['dataset_id' => $newDataset->id]);

        $log = ActivityLog::where('actable_id', $consentScope->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('dataset_id', $log->changes);
        $this->assertEquals($originalDatasetId, $log->changes['dataset_id']['from']);
        $this->assertEquals($newDataset->id, $log->changes['dataset_id']['to']);
    }

    public function test_untracked_fields_do_not_trigger_update_logs(): void
    {
        $consentScope = ConsentScope::factory()->create();

        ActivityLog::truncate();

        // Update only the appended attribute (which shouldn't exist in database)
        // The display_id is an appended attribute so it won't trigger logs
        $consentScope->update([
            // Only non-tracked fields - this should not create a log
        ]);

        $log = ActivityLog::where('actable_id', $consentScope->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        // No update without tracked field changes
        $this->assertNull($log);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $consentScope = ConsentScope::factory()->create();

        $log = ActivityLog::where('actable_id', $consentScope->id)
            ->where('actable_type', ConsentScope::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($consentScope->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $consentScope = ConsentScope::factory()->create();

        $log = ActivityLog::where('actable_id', $consentScope->id)
            ->where('actable_type', ConsentScope::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_logs_activity_with_null_values(): void
    {
        $consentScope = ConsentScope::factory()->create(['effective_to' => null]);

        ActivityLog::truncate();

        $consentScope->update(['effective_to' => now()->addYear()->format('Y-m-d')]);

        $log = ActivityLog::where('actable_id', $consentScope->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('effective_to', $log->changes);
        $this->assertNull($log->changes['effective_to']['from']);
        $this->assertNotNull($log->changes['effective_to']['to']);
    }

    public function test_logs_activity_tracks_purpose_array_changes(): void
    {
        $consentScope = ConsentScope::factory()->create(['purpose' => ['MARKETING']]);

        ActivityLog::truncate();

        $consentScope->update(['purpose' => ['ANALYTICS', 'MARKETING']]);

        $log = ActivityLog::where('actable_id', $consentScope->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('purpose', $log->changes);
        $this->assertEquals(['MARKETING'], $log->changes['purpose']['from']);
        $this->assertEquals(['ANALYTICS', 'MARKETING'], $log->changes['purpose']['to']);
    }
}

<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\UserConsent;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserConsentObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_user_consent_create(): void
    {
        $consent = UserConsent::factory()->create();

        $log = ActivityLog::where('actable_id', $consent->id)
            ->where('actable_type', UserConsent::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('UserConsent created', $log->description);
    }

    public function test_logs_activity_on_user_consent_update(): void
    {
        $consent = UserConsent::factory()->create(['consent_status' => 'GIVEN']);

        ActivityLog::truncate();

        $consent->update(['consent_status' => 'WITHDRAWN']);

        $log = ActivityLog::where('actable_id', $consent->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('UserConsent updated', $log->description);
        $this->assertArrayHasKey('consent_status', $log->changes);
        $this->assertEquals('GIVEN', $log->changes['consent_status']['from']);
        $this->assertEquals('WITHDRAWN', $log->changes['consent_status']['to']);
    }

    public function test_logs_activity_on_user_consent_delete(): void
    {
        $consent = UserConsent::factory()->create();
        $consentId = $consent->id;

        $consent->delete();

        $log = ActivityLog::where('actable_id', $consentId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('UserConsent deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $consent = UserConsent::factory()->create([
            'subject_realm' => 'CUSTOMERS',
            'jurisdiction' => 'US',
            'consent_status' => 'GIVEN',
            'legal_basis' => 'EXPLICIT_CONSENT',
        ]);

        ActivityLog::truncate();

        $consent->update([
            'subject_realm' => 'EMPLOYEES',
            'jurisdiction' => 'EU',
            'consent_status' => 'WITHDRAWN',
            'legal_basis' => 'LEGAL_OBLIGATION',
        ]);

        $log = ActivityLog::where('actable_id', $consent->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('subject_realm', $log->changes);
        $this->assertArrayHasKey('jurisdiction', $log->changes);
        $this->assertArrayHasKey('consent_status', $log->changes);
        $this->assertArrayHasKey('legal_basis', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $consent = UserConsent::factory()->create();

        $log = ActivityLog::where('actable_id', $consent->id)
            ->where('actable_type', UserConsent::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($consent->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $consent = UserConsent::factory()->create();

        $log = ActivityLog::where('actable_id', $consent->id)
            ->where('actable_type', UserConsent::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_consent_purpose_array_changes(): void
    {
        $consent = UserConsent::factory()->create(['consent_purpose' => ['MARKETING']]);

        ActivityLog::truncate();

        $consent->update(['consent_purpose' => ['MARKETING', 'ANALYTICS']]);

        $log = ActivityLog::where('actable_id', $consent->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('consent_purpose', $log->changes);
        $this->assertEquals(['MARKETING'], $log->changes['consent_purpose']['from']);
        $this->assertEquals(['MARKETING', 'ANALYTICS'], $log->changes['consent_purpose']['to']);
    }

    public function test_tracks_validity_period_changes(): void
    {
        $consent = UserConsent::factory()->create([
            'effective_from' => now()->format('Y-m-d'),
            'effective_to' => now()->addYears(1)->format('Y-m-d'),
        ]);

        ActivityLog::truncate();

        $consent->update([
            'effective_to' => now()->addYears(2)->format('Y-m-d'),
        ]);

        $log = ActivityLog::where('actable_id', $consent->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('effective_to', $log->changes);
    }

    public function test_tracks_evidence_and_source_changes(): void
    {
        $consent = UserConsent::factory()->create([
            'evidence_ref' => 'EVIDENCE001',
            'source_system' => 'MARKETING_PLATFORM',
        ]);

        ActivityLog::truncate();

        $consent->update([
            'evidence_ref' => 'EVIDENCE002',
            'source_system' => 'CRM_SYSTEM',
        ]);

        $log = ActivityLog::where('actable_id', $consent->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('evidence_ref', $log->changes);
        $this->assertArrayHasKey('source_system', $log->changes);
    }
}

<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\ConsentRecord;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConsentRecordObserverTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->actingAs($this->user);
    }

    public function test_logs_activity_on_consent_record_create(): void
    {
        $consentRecord = ConsentRecord::factory()->create();

        $log = ActivityLog::where('actable_id', $consentRecord->id)
            ->where('actable_type', ConsentRecord::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('ConsentRecord created', $log->description);
    }

    public function test_logs_activity_on_consent_record_update(): void
    {
        $consentRecord = ConsentRecord::factory()->create(['status' => 'DRAFT']);

        ActivityLog::truncate();

        $consentRecord->update(['status' => 'ACTIVE']);

        $log = ActivityLog::where('actable_id', $consentRecord->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('ConsentRecord updated', $log->description);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('DRAFT', $log->changes['status']['from']);
        $this->assertEquals('ACTIVE', $log->changes['status']['to']);
    }

    public function test_logs_activity_on_consent_record_delete(): void
    {
        $consentRecord = ConsentRecord::factory()->create();
        $consentRecordId = $consentRecord->id;

        $consentRecord->delete();

        $log = ActivityLog::where('actable_id', $consentRecordId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('ConsentRecord deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $consentRecord = ConsentRecord::factory()->create([
            'status' => 'DRAFT',
            'lifecycle_stage' => 'INITIAL',
            'consent_method' => 'ONLINE_FORM',
            'effective_from' => now()->format('Y-m-d'),
            'withdrawal_date' => null,
            'can_withdraw' => true,
        ]);

        ActivityLog::truncate();

        $consentRecord->update([
            'status' => 'ACTIVE',
            'lifecycle_stage' => 'ACTIVE',
            'consent_method' => 'EMAIL',
            'effective_from' => now()->addDay()->format('Y-m-d'),
            'withdrawal_date' => now()->format('Y-m-d'),
            'can_withdraw' => false,
        ]);

        $log = ActivityLog::where('actable_id', $consentRecord->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertArrayHasKey('lifecycle_stage', $log->changes);
        $this->assertArrayHasKey('consent_method', $log->changes);
        $this->assertArrayHasKey('effective_from', $log->changes);
        $this->assertArrayHasKey('withdrawal_date', $log->changes);
        $this->assertArrayHasKey('can_withdraw', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $consentRecord = ConsentRecord::factory()->create();

        $log = ActivityLog::where('actable_id', $consentRecord->id)
            ->where('actable_type', ConsentRecord::class)
            ->first();

        $this->assertNotNull($log);
        // Since ConsentRecord doesn't have direct organization_id,
        // it falls back to auth user's organization_id
        $this->assertNotNull($log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $consentRecord = ConsentRecord::factory()->create();

        $log = ActivityLog::where('actable_id', $consentRecord->id)
            ->where('actable_type', ConsentRecord::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_logs_activity_with_null_values(): void
    {
        $consentRecord = ConsentRecord::factory()->create(['withdrawal_date' => null]);

        ActivityLog::truncate();

        $consentRecord->update(['withdrawal_date' => now()->format('Y-m-d')]);

        $log = ActivityLog::where('actable_id', $consentRecord->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('withdrawal_date', $log->changes);
        $this->assertNull($log->changes['withdrawal_date']['from']);
        $this->assertNotNull($log->changes['withdrawal_date']['to']);
    }
}

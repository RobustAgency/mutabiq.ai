<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\PdpProcessingRegister;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PdpProcessingRegisterObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_pdp_processing_register_create(): void
    {
        $register = PdpProcessingRegister::factory()->create();

        $log = ActivityLog::where('actable_id', $register->id)
            ->where('actable_type', PdpProcessingRegister::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('PdpProcessingRegister created', $log->description);
    }

    public function test_logs_activity_on_pdp_processing_register_update(): void
    {
        $register = PdpProcessingRegister::factory()->create(['status' => 'ACTIVE']);

        ActivityLog::truncate();

        $register->update(['status' => 'ARCHIVED']);

        $log = ActivityLog::where('actable_id', $register->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('PdpProcessingRegister updated', $log->description);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('ACTIVE', $log->changes['status']['from']);
        $this->assertEquals('ARCHIVED', $log->changes['status']['to']);
    }

    public function test_logs_activity_on_pdp_processing_register_delete(): void
    {
        $register = PdpProcessingRegister::factory()->create();
        $registerId = $register->id;

        $register->delete();

        $log = ActivityLog::where('actable_id', $registerId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('PdpProcessingRegister deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $register = PdpProcessingRegister::factory()->create([
            'purpose' => 'Customer Management',
            'controller_role' => 'Data Controller',
            'lawful_basis' => 'CONSENT',
            'status' => 'ACTIVE',
        ]);

        ActivityLog::truncate();

        $register->update([
            'purpose' => 'Marketing',
            'controller_role' => 'Joint Controller',
            'lawful_basis' => 'LEGAL_OBLIGATION',
            'status' => 'UNDER_REVIEW',
        ]);

        $log = ActivityLog::where('actable_id', $register->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('purpose', $log->changes);
        $this->assertArrayHasKey('controller_role', $log->changes);
        $this->assertArrayHasKey('lawful_basis', $log->changes);
        $this->assertArrayHasKey('status', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $register = PdpProcessingRegister::factory()->create();

        $log = ActivityLog::where('actable_id', $register->id)
            ->where('actable_type', PdpProcessingRegister::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($register->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $register = PdpProcessingRegister::factory()->create();

        $log = ActivityLog::where('actable_id', $register->id)
            ->where('actable_type', PdpProcessingRegister::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_data_categories_array_changes(): void
    {
        $register = PdpProcessingRegister::factory()->create([
            'data_subject_categories' => ['CUSTOMERS'],
            'personal_data_categories' => ['NAME', 'EMAIL'],
        ]);

        ActivityLog::truncate();

        $register->update([
            'data_subject_categories' => ['CUSTOMERS', 'EMPLOYEES'],
            'personal_data_categories' => ['NAME', 'EMAIL', 'PHONE'],
        ]);

        $log = ActivityLog::where('actable_id', $register->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('data_subject_categories', $log->changes);
        $this->assertArrayHasKey('personal_data_categories', $log->changes);
    }

    public function test_tracks_recipients_array_changes(): void
    {
        $register = PdpProcessingRegister::factory()->create(['recipients' => ['INTERNAL']]);

        ActivityLog::truncate();

        $register->update(['recipients' => ['INTERNAL', 'THIRD_PARTY_VENDOR']]);

        $log = ActivityLog::where('actable_id', $register->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('recipients', $log->changes);
        $this->assertEquals(['INTERNAL'], $log->changes['recipients']['from']);
        $this->assertEquals(['INTERNAL', 'THIRD_PARTY_VENDOR'], $log->changes['recipients']['to']);
    }

    public function test_tracks_date_range_changes(): void
    {
        $register = PdpProcessingRegister::factory()->create([
            'effective_from' => now()->subMonths(6),
            'effective_to' => now()->addMonths(6),
        ]);

        ActivityLog::truncate();

        $register->update([
            'effective_to' => now()->addMonths(12),
        ]);

        $log = ActivityLog::where('actable_id', $register->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('effective_to', $log->changes);
    }

    public function test_tracks_dpia_and_security_references(): void
    {
        $register = PdpProcessingRegister::factory()->create([
            'dpia_required_flag' => false,
            'security_measures_ref' => 'SEC-001',
        ]);

        ActivityLog::truncate();

        $register->update([
            'dpia_required_flag' => true,
            'security_measures_ref' => 'SEC-002',
        ]);

        $log = ActivityLog::where('actable_id', $register->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('dpia_required_flag', $log->changes);
        $this->assertArrayHasKey('security_measures_ref', $log->changes);
    }
}

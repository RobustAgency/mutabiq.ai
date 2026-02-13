<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\Stakeholder;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StakeholderObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_stakeholder_create(): void
    {
        $stakeholder = Stakeholder::factory()->create();

        $log = ActivityLog::where('actable_id', $stakeholder->id)
            ->where('actable_type', Stakeholder::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('Stakeholder created', $log->description);
    }

    public function test_logs_activity_on_stakeholder_update(): void
    {
        $stakeholder = Stakeholder::factory()->create(['status' => 'ACTIVE']);

        ActivityLog::truncate();

        $stakeholder->update(['status' => 'INACTIVE']);

        $log = ActivityLog::where('actable_id', $stakeholder->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('Stakeholder updated', $log->description);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('ACTIVE', $log->changes['status']['from']);
        $this->assertEquals('INACTIVE', $log->changes['status']['to']);
    }

    public function test_logs_activity_on_stakeholder_delete(): void
    {
        $stakeholder = Stakeholder::factory()->create();
        $stakeholderId = $stakeholder->id;

        $stakeholder->delete();

        $log = ActivityLog::where('actable_id', $stakeholderId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('Stakeholder deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $stakeholder = Stakeholder::factory()->create([
            'type' => 'EMPLOYEE',
            'display_name' => 'Original Name',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'status' => 'ACTIVE',
        ]);

        ActivityLog::truncate();

        $stakeholder->update([
            'type' => 'CONTRACTOR',
            'display_name' => 'Updated Name',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'status' => 'INACTIVE',
        ]);

        $log = ActivityLog::where('actable_id', $stakeholder->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('type', $log->changes);
        $this->assertArrayHasKey('display_name', $log->changes);
        $this->assertArrayHasKey('first_name', $log->changes);
        $this->assertArrayHasKey('last_name', $log->changes);
        $this->assertArrayHasKey('email', $log->changes);
        $this->assertArrayHasKey('status', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $stakeholder = Stakeholder::factory()->create();

        $log = ActivityLog::where('actable_id', $stakeholder->id)
            ->where('actable_type', Stakeholder::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($stakeholder->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $stakeholder = Stakeholder::factory()->create();

        $log = ActivityLog::where('actable_id', $stakeholder->id)
            ->where('actable_type', Stakeholder::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_contact_information_changes(): void
    {
        $stakeholder = Stakeholder::factory()->create([
            'email' => 'original@example.com',
            'secondary_email' => 'secondary@example.com',
            'phone' => '123-456-7890',
            'mobile' => '098-765-4321',
        ]);

        ActivityLog::truncate();

        $stakeholder->update([
            'email' => 'updated@example.com',
            'secondary_email' => 'newsecondary@example.com',
            'phone' => '111-222-3333',
            'mobile' => '999-888-7777',
        ]);

        $log = ActivityLog::where('actable_id', $stakeholder->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('email', $log->changes);
        $this->assertArrayHasKey('secondary_email', $log->changes);
        $this->assertArrayHasKey('phone', $log->changes);
        $this->assertArrayHasKey('mobile', $log->changes);
    }

    public function test_tracks_organizational_information_changes(): void
    {
        $stakeholder = Stakeholder::factory()->create([
            'org_unit' => 'ORIGINAL_UNIT',
            'cost_center' => 'CC001',
            'manager' => 'manager@example.com',
            'employee_id' => 'EMP001',
        ]);

        ActivityLog::truncate();

        $stakeholder->update([
            'org_unit' => 'UPDATED_UNIT',
            'cost_center' => 'CC002',
            'manager' => 'newmanager@example.com',
            'employee_id' => 'EMP002',
        ]);

        $log = ActivityLog::where('actable_id', $stakeholder->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('org_unit', $log->changes);
        $this->assertArrayHasKey('cost_center', $log->changes);
        $this->assertArrayHasKey('manager', $log->changes);
        $this->assertArrayHasKey('employee_id', $log->changes);
    }

    public function test_tracks_role_tags_array_changes(): void
    {
        $stakeholder = Stakeholder::factory()->create(['role_tags' => ['AUDITOR']]);

        ActivityLog::truncate();

        $stakeholder->update(['role_tags' => ['AUDITOR', 'COMPLIANCE_OFFICER']]);

        $log = ActivityLog::where('actable_id', $stakeholder->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('role_tags', $log->changes);
        $this->assertEquals(['AUDITOR'], $log->changes['role_tags']['from']);
        $this->assertEquals(['AUDITOR', 'COMPLIANCE_OFFICER'], $log->changes['role_tags']['to']);
    }

    public function test_tracks_date_range_changes(): void
    {
        $stakeholder = Stakeholder::factory()->create([
            'start_date' => now()->subMonths(6)->format('Y-m-d'),
            'end_date' => now()->addMonths(6)->format('Y-m-d'),
        ]);

        ActivityLog::truncate();

        $stakeholder->update([
            'end_date' => now()->addMonths(12)->format('Y-m-d'),
        ]);

        $log = ActivityLog::where('actable_id', $stakeholder->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('end_date', $log->changes);
    }
}

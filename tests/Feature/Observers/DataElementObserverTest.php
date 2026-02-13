<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\DataElement;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataElementObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_data_element_create(): void
    {
        $dataElement = DataElement::factory()->create();

        $log = ActivityLog::where('actable_id', $dataElement->id)
            ->where('actable_type', DataElement::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('DataElement created', $log->description);
    }

    public function test_logs_activity_on_data_element_update(): void
    {
        $dataElement = DataElement::factory()->create(['status' => 'DRAFT']);

        ActivityLog::truncate();

        $dataElement->update(['status' => 'ACTIVE']);

        $log = ActivityLog::where('actable_id', $dataElement->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('DataElement updated', $log->description);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('DRAFT', $log->changes['status']['from']);
        $this->assertEquals('ACTIVE', $log->changes['status']['to']);
    }

    public function test_logs_activity_on_data_element_delete(): void
    {
        $dataElement = DataElement::factory()->create();
        $dataElementId = $dataElement->id;

        $dataElement->delete();

        $log = ActivityLog::where('actable_id', $dataElementId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('DataElement deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $dataElement = DataElement::factory()->create([
            'name' => 'Original Name',
            'sensitivity' => 'LOW',
            'data_type' => 'STRING',
            'contains_personal_data' => false,
        ]);

        ActivityLog::truncate();

        $dataElement->update([
            'name' => 'Updated Name',
            'sensitivity' => 'HIGH',
            'data_type' => 'INTEGER',
            'contains_personal_data' => true,
        ]);

        $log = ActivityLog::where('actable_id', $dataElement->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('name', $log->changes);
        $this->assertArrayHasKey('sensitivity', $log->changes);
        $this->assertArrayHasKey('data_type', $log->changes);
        $this->assertArrayHasKey('contains_personal_data', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $dataElement = DataElement::factory()->create();

        $log = ActivityLog::where('actable_id', $dataElement->id)
            ->where('actable_type', DataElement::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($dataElement->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $dataElement = DataElement::factory()->create();

        $log = ActivityLog::where('actable_id', $dataElement->id)
            ->where('actable_type', DataElement::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_cde_categories_array_changes(): void
    {
        $dataElement = DataElement::factory()->create(['cde_categories' => ['PII']]);

        ActivityLog::truncate();

        $dataElement->update(['cde_categories' => ['PII', 'SENSITIVE']]);

        $log = ActivityLog::where('actable_id', $dataElement->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('cde_categories', $log->changes);
        $this->assertEquals(['PII'], $log->changes['cde_categories']['from']);
        $this->assertEquals(['PII', 'SENSITIVE'], $log->changes['cde_categories']['to']);
    }

    public function test_tracks_boolean_field_changes(): void
    {
        $dataElement = DataElement::factory()->create([
            'contains_personal_data' => true,
            'contains_sensitive_data' => false,
        ]);

        ActivityLog::truncate();

        $dataElement->update([
            'contains_personal_data' => false,
            'contains_sensitive_data' => true,
        ]);

        $log = ActivityLog::where('actable_id', $dataElement->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertTrue($log->changes['contains_personal_data']['from']);
        $this->assertFalse($log->changes['contains_personal_data']['to']);
        $this->assertFalse($log->changes['contains_sensitive_data']['from']);
        $this->assertTrue($log->changes['contains_sensitive_data']['to']);
    }
}

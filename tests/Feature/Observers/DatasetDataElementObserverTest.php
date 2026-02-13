<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\Dataset;
use App\Models\ActivityLog;
use App\Models\DataElement;
use App\Models\DatasetDataElement;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatasetDataElementObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_dataset_data_element_create(): void
    {
        $dataset = Dataset::factory()->create();
        $dataElement = DataElement::factory()->create();
        $datasetDataElement = DatasetDataElement::factory()->create([
            'dataset_id' => $dataset->id,
            'data_element_id' => $dataElement->id,
        ]);

        $log = ActivityLog::where('actable_id', $datasetDataElement->id)
            ->where('actable_type', DatasetDataElement::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('DatasetDataElement created', $log->description);
    }

    public function test_logs_activity_on_dataset_data_element_update(): void
    {
        $dataset = Dataset::factory()->create();
        $dataElement = DataElement::factory()->create();
        $datasetDataElement = DatasetDataElement::factory()->create([
            'dataset_id' => $dataset->id,
            'data_element_id' => $dataElement->id,
            'nullable' => false,
        ]);

        ActivityLog::truncate();

        $datasetDataElement->update(['nullable' => true]);

        $log = ActivityLog::where('actable_id', $datasetDataElement->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('DatasetDataElement updated', $log->description);
        $this->assertArrayHasKey('nullable', $log->changes);
    }

    public function test_logs_activity_on_dataset_data_element_delete(): void
    {
        $datasetDataElement = DatasetDataElement::factory()->create();
        $datasetDataElementId = $datasetDataElement->id;

        $datasetDataElement->delete();

        $log = ActivityLog::where('actable_id', $datasetDataElementId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('DatasetDataElement deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $datasetDataElement = DatasetDataElement::factory()->create([
            'column_name' => 'original_column',
            'sensitivity_override' => 'LOW',
            'pii_override' => false,
            'deprecated' => false,
        ]);

        ActivityLog::truncate();

        $datasetDataElement->update([
            'column_name' => 'updated_column',
            'sensitivity_override' => 'HIGH',
            'pii_override' => true,
            'deprecated' => true,
        ]);

        $log = ActivityLog::where('actable_id', $datasetDataElement->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('column_name', $log->changes);
        $this->assertArrayHasKey('sensitivity_override', $log->changes);
        $this->assertArrayHasKey('pii_override', $log->changes);
        $this->assertArrayHasKey('deprecated', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $datasetDataElement = DatasetDataElement::factory()->create();

        $log = ActivityLog::where('actable_id', $datasetDataElement->id)
            ->where('actable_type', DatasetDataElement::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($datasetDataElement->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $datasetDataElement = DatasetDataElement::factory()->create();

        $log = ActivityLog::where('actable_id', $datasetDataElement->id)
            ->where('actable_type', DatasetDataElement::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_transform_applied_changes(): void
    {
        $datasetDataElement = DatasetDataElement::factory()->create(['transform_applied' => false]);

        ActivityLog::truncate();

        $datasetDataElement->update(['transform_applied' => true]);

        $log = ActivityLog::where('actable_id', $datasetDataElement->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('transform_applied', $log->changes);
    }

    public function test_tracks_cde_category_changes(): void
    {
        $datasetDataElement = DatasetDataElement::factory()->create([
            'cde_in_dataset' => false,
            'cde_category_in_dataset' => null,
        ]);

        ActivityLog::truncate();

        $datasetDataElement->update([
            'cde_in_dataset' => true,
            'cde_category_in_dataset' => 'PII',
        ]);

        $log = ActivityLog::where('actable_id', $datasetDataElement->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('cde_in_dataset', $log->changes);
        $this->assertArrayHasKey('cde_category_in_dataset', $log->changes);
    }
}

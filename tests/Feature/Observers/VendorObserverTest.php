<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\Vendor;
use App\Models\ActivityLog;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VendorObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_vendor_create(): void
    {
        $vendor = Vendor::factory()->create();

        $log = ActivityLog::where('actable_id', $vendor->id)
            ->where('actable_type', Vendor::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('Vendor created', $log->description);
    }

    public function test_logs_activity_on_vendor_update(): void
    {
        $vendor = Vendor::factory()->create(['status' => 'ACTIVE']);

        ActivityLog::truncate();

        $vendor->update(['status' => 'SUSPENDED']);

        $log = ActivityLog::where('actable_id', $vendor->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('Vendor updated', $log->description);
        $this->assertArrayHasKey('status', $log->changes);
        $this->assertEquals('ACTIVE', $log->changes['status']['from']);
        $this->assertEquals('SUSPENDED', $log->changes['status']['to']);
    }

    public function test_logs_activity_on_vendor_delete(): void
    {
        $vendor = Vendor::factory()->create();
        $vendorId = $vendor->id;

        $vendor->delete();

        $log = ActivityLog::where('actable_id', $vendorId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('Vendor deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $vendor = Vendor::factory()->create([
            'vendor_name' => 'Original Vendor',
            'legal_name' => 'Original Legal',
            'hq_country' => 'US',
            'risk_tier' => 'LOW',
            'status' => 'ACTIVE',
        ]);

        ActivityLog::truncate();

        $vendor->update([
            'vendor_name' => 'Updated Vendor',
            'legal_name' => 'Updated Legal',
            'hq_country' => 'UK',
            'risk_tier' => 'HIGH',
            'status' => 'SUSPENDED',
        ]);

        $log = ActivityLog::where('actable_id', $vendor->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('vendor_name', $log->changes);
        $this->assertArrayHasKey('legal_name', $log->changes);
        $this->assertArrayHasKey('hq_country', $log->changes);
        $this->assertArrayHasKey('risk_tier', $log->changes);
        $this->assertArrayHasKey('status', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $vendor = Vendor::factory()->create();

        $log = ActivityLog::where('actable_id', $vendor->id)
            ->where('actable_type', Vendor::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($vendor->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $vendor = Vendor::factory()->create();

        $log = ActivityLog::where('actable_id', $vendor->id)
            ->where('actable_type', Vendor::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_tracks_type_array_changes(): void
    {
        $vendor = Vendor::factory()->create(['type' => ['SOFTWARE_PROVIDER']]);

        ActivityLog::truncate();

        $vendor->update(['type' => ['SOFTWARE_PROVIDER', 'MANAGED_SERVICES']]);

        $log = ActivityLog::where('actable_id', $vendor->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('type', $log->changes);
        $this->assertEquals(['SOFTWARE_PROVIDER'], $log->changes['type']['from']);
        $this->assertEquals(['SOFTWARE_PROVIDER', 'MANAGED_SERVICES'], $log->changes['type']['to']);
    }

    public function test_tracks_data_processing_and_service_changes(): void
    {
        $vendor = Vendor::factory()->create([
            'data_processing_role' => 'PROCESSOR',
            'service_provided' => 'Cloud Hosting',
        ]);

        ActivityLog::truncate();

        $vendor->update([
            'data_processing_role' => 'JOINT_CONTROLLER',
            'service_provided' => 'Cloud Hosting & Analytics',
        ]);

        $log = ActivityLog::where('actable_id', $vendor->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('data_processing_role', $log->changes);
        $this->assertArrayHasKey('service_provided', $log->changes);
    }

    public function test_tracks_registration_identifiers_changes(): void
    {
        $vendor = Vendor::factory()->create([
            'duns_number' => 'DUNS001',
            'lei_number' => 'LEI001',
            'tax_id' => 'TAX001',
            'stock_ticker' => 'TICK001',
        ]);

        ActivityLog::truncate();

        $vendor->update([
            'duns_number' => 'DUNS002',
            'lei_number' => 'LEI002',
            'tax_id' => 'TAX002',
            'stock_ticker' => 'TICK002',
        ]);

        $log = ActivityLog::where('actable_id', $vendor->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('duns_number', $log->changes);
        $this->assertArrayHasKey('lei_number', $log->changes);
        $this->assertArrayHasKey('tax_id', $log->changes);
        $this->assertArrayHasKey('stock_ticker', $log->changes);
    }

    public function test_tracks_primary_contacts_array_changes(): void
    {
        $vendor = Vendor::factory()->create([
            'primary_contacts' => ['contact1@vendor.com'],
        ]);

        ActivityLog::truncate();

        $vendor->update([
            'primary_contacts' => ['contact1@vendor.com', 'contact2@vendor.com'],
        ]);

        $log = ActivityLog::where('actable_id', $vendor->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('primary_contacts', $log->changes);
    }

    public function test_tracks_metadata_array_changes(): void
    {
        $vendor = Vendor::factory()->create([
            'metadata' => ['key1' => 'value1'],
        ]);

        ActivityLog::truncate();

        $vendor->update([
            'metadata' => ['key1' => 'value1', 'key2' => 'value2'],
        ]);

        $log = ActivityLog::where('actable_id', $vendor->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('metadata', $log->changes);
    }
}

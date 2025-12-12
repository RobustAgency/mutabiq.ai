<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\PrivacyIncident;
use App\Enums\PrivacyIncident\Status;
use App\Enums\PrivacyIncident\RiskLevel;
use App\Enums\PrivacyIncident\IncidentType;
use App\Repositories\PrivacyIncidentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PrivacyIncidentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PrivacyIncidentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PrivacyIncidentRepository::class);
    }

    /**
     * Test getting filtered privacy incidents with default pagination
     */
    public function test_get_filtered_privacy_incidents_with_default_pagination(): void
    {
        PrivacyIncident::factory(20)->create();

        $result = $this->repository->getFilteredPrivacyIncidents([]);

        $this->assertEquals(15, $result->count());
        $this->assertEquals(20, $result->total());
    }

    /**
     * Test getting filtered privacy incidents with custom per_page
     */
    public function test_get_filtered_privacy_incidents_with_custom_per_page(): void
    {
        PrivacyIncident::factory(25)->create();

        $result = $this->repository->getFilteredPrivacyIncidents(['per_page' => 10]);

        $this->assertEquals(10, $result->count());
        $this->assertEquals(25, $result->total());
    }

    /**
     * Test filtering by incident_type
     */
    public function test_filter_by_incident_type(): void
    {
        PrivacyIncident::factory(5)->create(['incident_type' => IncidentType::DATA_EXPOSURE->value]);
        PrivacyIncident::factory(3)->create(['incident_type' => IncidentType::UNAUTHORIZED_ACCESS->value]);

        $result = $this->repository->getFilteredPrivacyIncidents([
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
        ]);

        $this->assertEquals(5, $result->total());
        $this->assertTrue($result->every(fn ($incident) => $incident->incident_type === IncidentType::DATA_EXPOSURE->value));
    }

    /**
     * Test filtering by risk_level
     */
    public function test_filter_by_risk_level(): void
    {
        PrivacyIncident::factory(4)->create(['risk_level' => RiskLevel::HIGH->value]);
        PrivacyIncident::factory(2)->create(['risk_level' => RiskLevel::LOW->value]);

        $result = $this->repository->getFilteredPrivacyIncidents([
            'risk_level' => RiskLevel::HIGH->value,
        ]);

        $this->assertEquals(4, $result->total());
        $this->assertTrue($result->every(fn ($incident) => $incident->risk_level === RiskLevel::HIGH->value));
    }

    /**
     * Test filtering by status
     */
    public function test_filter_by_status(): void
    {
        PrivacyIncident::factory(6)->create(['status' => Status::RESOLVED->value]);
        PrivacyIncident::factory(4)->create(['status' => Status::DETECTED->value]);

        $result = $this->repository->getFilteredPrivacyIncidents([
            'status' => Status::RESOLVED->value,
        ]);

        $this->assertEquals(6, $result->total());
        $this->assertTrue($result->every(fn ($incident) => $incident->status === Status::RESOLVED->value));
    }

    /**
     * Test filtering by is_breach true
     */
    public function test_filter_by_is_breach_true(): void
    {
        PrivacyIncident::factory(7)->create(['is_breach' => true]);
        PrivacyIncident::factory(3)->create(['is_breach' => false]);

        $result = $this->repository->getFilteredPrivacyIncidents([
            'is_breach' => true,
        ]);

        $this->assertEquals(7, $result->total());
        $this->assertTrue($result->every(fn ($incident) => $incident->is_breach === true));
    }

    /**
     * Test filtering by is_breach false
     */
    public function test_filter_by_is_breach_false(): void
    {
        PrivacyIncident::factory(5)->create(['is_breach' => true]);
        PrivacyIncident::factory(8)->create(['is_breach' => false]);

        $result = $this->repository->getFilteredPrivacyIncidents([
            'is_breach' => false,
        ]);

        $this->assertEquals(8, $result->total());
        $this->assertTrue($result->every(fn ($incident) => $incident->is_breach === false));
    }

    /**
     * Test filtering by multiple filters combined
     */
    public function test_filter_by_multiple_filters(): void
    {
        PrivacyIncident::factory(10)->create([
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'status' => Status::RESOLVED->value,
            'is_breach' => true,
        ]);

        PrivacyIncident::factory(5)->create([
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::LOW->value,
        ]);

        $result = $this->repository->getFilteredPrivacyIncidents([
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'status' => Status::RESOLVED->value,
            'is_breach' => true,
        ]);

        $this->assertEquals(10, $result->total());
    }

    /**
     * Test filtering with no matching results
     */
    public function test_filter_with_no_matching_results(): void
    {
        PrivacyIncident::factory(5)->create(['incident_type' => IncidentType::DATA_EXPOSURE->value]);

        $result = $this->repository->getFilteredPrivacyIncidents([
            'incident_type' => IncidentType::UNAUTHORIZED_ACCESS->value,
        ]);

        $this->assertEquals(0, $result->total());
    }

    /**
     * Test create privacy incident
     */
    public function test_create_privacy_incident(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $data = [
            'organization_id' => $organization->id,
            'incident_code' => 'INC-001',
            'incident_title' => 'Test Incident',
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'is_breach' => true,
            'detected_date' => now(),
            'incident_description' => 'Test description',
            'what_happened' => 'Test what happened',
            'how_discovered' => 'Test how discovered',
            'data_compromised' => 'Test data compromised',
            'data_categories_affected' => ['personal_data'],
            'estimated_affected_subjects' => 100,
            'notification_required' => 'yes',
            'notification_status' => 'pending',
            'authority_notified' => false,
            'subjects_notified' => false,
            'immediate_actions' => 'Test actions',
            'mitigation_measures' => 'Test mitigation',
            'preventive_measures' => 'Test prevention',
            'status' => Status::DETECTED->value,
            'affected_systems' => ['CRM'],
            'third_party_involved' => false,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ];

        $incident = $this->repository->createPrivacyIncident($data);

        $this->assertInstanceOf(PrivacyIncident::class, $incident);
        $this->assertEquals('INC-001', $incident->incident_code);
        $this->assertEquals(IncidentType::DATA_EXPOSURE->value, $incident->incident_type);
        $this->assertTrue($incident->is_breach);
    }

    /**
     * Test create privacy incident with minimal data
     */
    public function test_create_privacy_incident_with_minimal_data(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $data = [
            'organization_id' => $organization->id,
            'incident_code' => 'INC-MIN-001',
            'incident_title' => 'Minimal Incident',
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::LOW->value,
            'is_breach' => false,
            'detected_date' => now(),
            'incident_description' => 'Minimal description',
            'what_happened' => 'Minimal what happened',
            'how_discovered' => 'Minimal how discovered',
            'data_compromised' => 'Minimal data',
            'data_categories_affected' => ['personal_data'],
            'estimated_affected_subjects' => 1,
            'notification_required' => 'no',
            'notification_status' => 'pending',
            'authority_notified' => false,
            'subjects_notified' => false,
            'immediate_actions' => 'Minimal actions',
            'mitigation_measures' => 'Minimal mitigation',
            'preventive_measures' => 'Minimal prevention',
            'status' => Status::DETECTED->value,
            'affected_systems' => ['Database'],
            'third_party_involved' => false,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ];

        $incident = $this->repository->createPrivacyIncident($data);

        $this->assertNotNull($incident->id);
        $this->assertEquals('INC-MIN-001', $incident->incident_code);
    }

    /**
     * Test update privacy incident with full data
     */
    public function test_update_privacy_incident_full(): void
    {
        $incident = PrivacyIncident::factory()->create([
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::LOW->value,
        ]);

        $updateData = [
            'incident_title' => 'Updated Title',
            'incident_type' => IncidentType::UNAUTHORIZED_ACCESS->value,
            'risk_level' => RiskLevel::HIGH->value,
            'status' => Status::RESOLVED->value,
        ];

        $updated = $this->repository->updatePrivacyIncident($incident, $updateData);

        $this->assertEquals('Updated Title', $updated->incident_title);
        $this->assertEquals(IncidentType::UNAUTHORIZED_ACCESS->value, $updated->incident_type);
        $this->assertEquals(RiskLevel::HIGH->value, $updated->risk_level);
        $this->assertEquals(Status::RESOLVED->value, $updated->status);
    }

    /**
     * Test update privacy incident with partial data
     */
    public function test_update_privacy_incident_partial(): void
    {
        $originalTitle = 'Original Title';
        $incident = PrivacyIncident::factory()->create([
            'incident_title' => $originalTitle,
            'status' => Status::DETECTED->value,
        ]);

        $updateData = [
            'status' => Status::UNDER_INVESTIGATION->value,
        ];

        $updated = $this->repository->updatePrivacyIncident($incident, $updateData);

        $this->assertEquals($originalTitle, $updated->incident_title);
        $this->assertEquals(Status::UNDER_INVESTIGATION->value, $updated->status);
    }

    /**
     * Test update returns fresh instance
     */
    public function test_update_returns_fresh_instance(): void
    {
        $incident = PrivacyIncident::factory()->create([
            'status' => Status::DETECTED->value,
        ]);

        $updated = $this->repository->updatePrivacyIncident($incident, [
            'status' => Status::RESOLVED->value,
        ]);

        $this->assertNotSame($incident, $updated);
        $this->assertEquals(Status::RESOLVED->value, $updated->status);
    }

    /**
     * Test delete privacy incident
     */
    public function test_delete_privacy_incident(): void
    {
        $incident = PrivacyIncident::factory()->create();
        $id = $incident->id;

        $result = $this->repository->deletePrivacyIncident($incident);

        $this->assertTrue($result);
        $this->assertNull(PrivacyIncident::find($id));
    }

    /**
     * Test delete non-existent incident returns false
     */
    public function test_delete_returns_false_for_deleted_incident(): void
    {
        $incident = PrivacyIncident::factory()->create();
        $incident->delete();

        $result = $this->repository->deletePrivacyIncident($incident);

        $this->assertFalse($result);
    }

    /**
     * Test filter with all IncidentType enum values
     */
    public function test_filter_with_all_incident_types(): void
    {
        foreach (IncidentType::cases() as $type) {
            PrivacyIncident::factory()->create(['incident_type' => $type->value]);
        }

        foreach (IncidentType::cases() as $type) {
            $result = $this->repository->getFilteredPrivacyIncidents([
                'incident_type' => $type->value,
            ]);

            $this->assertEquals(1, $result->total());
            $this->assertEquals($type->value, $result->first()->incident_type);
        }
    }

    /**
     * Test filter with all RiskLevel enum values
     */
    public function test_filter_with_all_risk_levels(): void
    {
        foreach (RiskLevel::cases() as $level) {
            PrivacyIncident::factory()->create(['risk_level' => $level->value]);
        }

        foreach (RiskLevel::cases() as $level) {
            $result = $this->repository->getFilteredPrivacyIncidents([
                'risk_level' => $level->value,
            ]);

            $this->assertEquals(1, $result->total());
            $this->assertEquals($level->value, $result->first()->risk_level);
        }
    }

    /**
     * Test filter with all Status enum values
     */
    public function test_filter_with_all_statuses(): void
    {
        foreach (Status::cases() as $status) {
            PrivacyIncident::factory()->create(['status' => $status->value]);
        }

        foreach (Status::cases() as $status) {
            $result = $this->repository->getFilteredPrivacyIncidents([
                'status' => $status->value,
            ]);

            $this->assertEquals(1, $result->total());
            $this->assertEquals($status->value, $result->first()->status);
        }
    }
}

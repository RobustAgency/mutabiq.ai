<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\PrivacyIncident;
use App\Enums\PrivacyIncident\Status;
use App\Enums\PrivacyIncident\RiskLevel;
use App\Enums\PrivacyIncident\IncidentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\PrivacyIncident\NotificationRequired;
use App\Enums\RecordOfProcessingActivity\DataCategory;

class PrivacyIncidentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->actingAs($this->user);
    }

    /**
     * Test index retrieves all privacy incidents
     */
    public function test_index_retrieves_all_privacy_incidents(): void
    {
        PrivacyIncident::factory(5)->create(['organization_id' => $this->organization->id]);

        $response = $this->getJson('/api/privacy-incidents');

        $response->assertStatus(200);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('message', 'Privacy incidents retrieved successfully');
        $this->assertCount(5, $response->json('data.data'));
    }

    /**
     * Test index with empty list
     */
    public function test_index_with_empty_list(): void
    {
        $response = $this->getJson('/api/privacy-incidents');

        $response->assertStatus(200);
        $response->assertJsonPath('error', false);
        $this->assertCount(0, $response->json('data.data'));
    }

    /**
     * Test index with pagination
     */
    public function test_index_with_pagination(): void
    {
        PrivacyIncident::factory(25)->create(['organization_id' => $this->organization->id]);

        $response = $this->getJson('/api/privacy-incidents?per_page=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data.data'));
        $this->assertEquals(25, $response->json('data.total'));
    }

    /**
     * Test index filter by incident_type
     */
    public function test_index_filter_by_incident_type(): void
    {
        PrivacyIncident::factory(3)->create([
            'organization_id' => $this->organization->id,
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
        ]);
        PrivacyIncident::factory(2)->create([
            'organization_id' => $this->organization->id,
            'incident_type' => IncidentType::UNAUTHORIZED_ACCESS->value,
        ]);

        $response = $this->getJson('/api/privacy-incidents?incident_type=data_exposure');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    /**
     * Test index filter by risk_level
     */
    public function test_index_filter_by_risk_level(): void
    {
        PrivacyIncident::factory(4)->create([
            'organization_id' => $this->organization->id,
            'risk_level' => RiskLevel::HIGH->value,
        ]);
        PrivacyIncident::factory(2)->create([
            'organization_id' => $this->organization->id,
            'risk_level' => RiskLevel::LOW->value,
        ]);

        $response = $this->getJson('/api/privacy-incidents?risk_level=high');

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data.data'));
    }

    /**
     * Test index filter by status
     */
    public function test_index_filter_by_status(): void
    {
        PrivacyIncident::factory(6)->create([
            'organization_id' => $this->organization->id,
            'status' => Status::RESOLVED->value,
        ]);
        PrivacyIncident::factory(3)->create([
            'organization_id' => $this->organization->id,
            'status' => Status::DETECTED->value,
        ]);

        $response = $this->getJson('/api/privacy-incidents?status=resolved');

        $response->assertStatus(200);
        $this->assertCount(6, $response->json('data.data'));
    }

    /**
     * Test index filter by is_breach
     */
    public function test_index_filter_by_is_breach(): void
    {
        PrivacyIncident::factory(7)->create([
            'organization_id' => $this->organization->id,
            'is_breach' => true,
        ]);
        PrivacyIncident::factory(3)->create([
            'organization_id' => $this->organization->id,
            'is_breach' => false,
        ]);

        $response = $this->getJson('/api/privacy-incidents?is_breach=1');

        $response->assertStatus(200);
        $this->assertCount(7, $response->json('data.data'));
    }

    /**
     * Test index with multiple filters
     */
    public function test_index_with_multiple_filters(): void
    {
        PrivacyIncident::factory(5)->create([
            'organization_id' => $this->organization->id,
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'status' => Status::RESOLVED->value,
            'is_breach' => true,
        ]);

        PrivacyIncident::factory(8)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson('/api/privacy-incidents?incident_type=data_exposure&risk_level=high&status=resolved&is_breach=1');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
    }

    /**
     * Test store creates privacy incident
     */
    public function test_store_creates_privacy_incident(): void
    {
        $data = [
            'incident_title' => 'Test Incident',
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'is_breach' => true,
            'breach_criteria_met' => ['high_risk'],
            'detected_date' => now()->toDateString(),
            'incident_description' => 'Test description',
            'what_happened' => 'Test what happened',
            'how_discovered' => 'Test how discovered',
            'data_compromised' => 'Test data compromised',
            'data_categories_affected' => [DataCategory::NAME->value],
            'estimated_affected_subjects' => 100,
            'notification_required' => NotificationRequired::BOTH->value,
            'notification_status' => 'pending',
            'authority_notified' => false,
            'subjects_notified' => false,
            'immediate_actions' => 'Test actions',
            'mitigation_measures' => 'Test mitigation',
            'preventive_measures' => 'Test prevention',
            'status' => Status::DETECTED->value,
            'affected_systems' => ['CRM', 'Database'],
            'third_party_involved' => false,
        ];

        $response = $this->postJson('/api/privacy-incidents', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('message', 'Privacy incident created successfully');
        $response->assertJsonPath('data.incident_title', 'Test Incident');
        $response->assertJsonPath('data.organization_id', $this->organization->id);
        $response->assertJsonPath('data.created_by', $this->user->id);
        $response->assertJsonPath('data.updated_by', $this->user->id);

        $this->assertDatabaseHas('privacy_incidents', [
            'incident_title' => 'Test Incident',
            'organization_id' => $this->organization->id,
            'created_by' => $this->user->id,
        ]);
    }

    /**
     * Test store sets notification_deadline
     */
    public function test_store_sets_notification_deadline(): void
    {
        $detectedDate = now()->subDays(1);

        $data = [
            'incident_title' => 'Test Incident',
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'is_breach' => true,
            'breach_criteria_met' => ['high_risk'],
            'detected_date' => $detectedDate->toDateString(),
            'incident_description' => 'Test',
            'what_happened' => 'Test',
            'how_discovered' => 'Test',
            'data_compromised' => 'Test',
            'data_categories_affected' => [DataCategory::NAME->value],
            'estimated_affected_subjects' => 100,
            'notification_required' => NotificationRequired::BOTH->value,
            'notification_status' => 'pending',
            'authority_notified' => false,
            'subjects_notified' => false,
            'immediate_actions' => 'Test',
            'mitigation_measures' => 'Test',
            'preventive_measures' => 'Test',
            'status' => Status::DETECTED->value,
            'affected_systems' => ['CRM'],
            'third_party_involved' => false,
        ];

        $response = $this->postJson('/api/privacy-incidents', $data);

        $response->assertStatus(201);
        $this->assertNotNull($response->json('data.notification_deadline'));
    }

    /**
     * Test store calculates days_to_resolution
     */
    public function test_store_calculates_days_to_resolution(): void
    {
        $detectedDate = now()->subDays(5);
        $resolutionDate = now();

        $data = [
            'incident_title' => 'Test Incident',
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'is_breach' => true,
            'breach_criteria_met' => ['high_risk'],
            'detected_date' => $detectedDate->toDateString(),
            'resolution_date' => $resolutionDate->toDateString(),
            'incident_description' => 'Test',
            'what_happened' => 'Test',
            'how_discovered' => 'Test',
            'data_compromised' => 'Test',
            'data_categories_affected' => [DataCategory::HEALTH->value],
            'estimated_affected_subjects' => 100,
            'notification_required' => NotificationRequired::BOTH->value,
            'notification_status' => 'pending',
            'authority_notified' => false,
            'subjects_notified' => false,
            'immediate_actions' => 'Test',
            'mitigation_measures' => 'Test',
            'preventive_measures' => 'Test',
            'status' => Status::RESOLVED->value,
            'root_cause_analysis' => 'Test RCA',
            'lessons_learned' => 'Test lessons',
            'affected_systems' => ['CRM'],
            'third_party_involved' => false,
        ];

        $response = $this->postJson('/api/privacy-incidents', $data);

        $response->assertStatus(201);
        $this->assertGreaterThan(0, $response->json('data.days_to_resolution'));
    }

    /**
     * Test store with validation errors
     */
    public function test_store_with_validation_errors(): void
    {
        $data = [
            'incident_title' => '',
            'incident_type' => 'invalid_type',
            'risk_level' => 'invalid_level',
        ];

        $response = $this->postJson('/api/privacy-incidents', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['incident_title', 'incident_type', 'risk_level']);
    }

    /**
     * Test store with conditional validation for is_breach
     */
    public function test_store_requires_breach_criteria_when_is_breach_true(): void
    {
        $data = [
            'incident_title' => 'Test Incident',
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'is_breach' => true,
            'detected_date' => now()->toDateString(),
            'incident_description' => 'Test',
            'what_happened' => 'Test',
            'how_discovered' => 'Test',
            'data_compromised' => 'Test',
            'data_categories_affected' => [DataCategory::NAME->value],
            'estimated_affected_subjects' => 100,
            'notification_required' => NotificationRequired::BOTH->value,
            'notification_status' => 'pending',
            'authority_notified' => false,
            'subjects_notified' => false,
            'immediate_actions' => 'Test',
            'mitigation_measures' => 'Test',
            'preventive_measures' => 'Test',
            'status' => Status::DETECTED->value,
            'affected_systems' => ['CRM'],
            'third_party_involved' => false,
        ];

        $response = $this->postJson('/api/privacy-incidents', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['breach_criteria_met']);
    }

    /**
     * Test store with conditional validation for authority_notified
     */
    public function test_store_requires_authority_fields_when_notified(): void
    {
        $data = [
            'incident_title' => 'Test Incident',
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'is_breach' => false,
            'detected_date' => now()->toDateString(),
            'incident_description' => 'Test',
            'what_happened' => 'Test',
            'how_discovered' => 'Test',
            'data_compromised' => 'Test',
            'data_categories_affected' => [DataCategory::NAME->value],
            'estimated_affected_subjects' => 100,
            'notification_required' => NotificationRequired::BOTH->value,
            'notification_status' => 'pending',
            'authority_notified' => true,
            'subjects_notified' => false,
            'immediate_actions' => 'Test',
            'mitigation_measures' => 'Test',
            'preventive_measures' => 'Test',
            'status' => Status::DETECTED->value,
            'affected_systems' => ['CRM'],
            'third_party_involved' => false,
        ];

        $response = $this->postJson('/api/privacy-incidents', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['authority_notification_date', 'supervisory_authority']);
    }

    /**
     * Test store with conditional validation for subjects_notified
     */
    public function test_store_requires_notification_fields_when_subjects_notified(): void
    {
        $data = [
            'incident_title' => 'Test Incident',
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'is_breach' => false,
            'detected_date' => now()->toDateString(),
            'incident_description' => 'Test',
            'what_happened' => 'Test',
            'how_discovered' => 'Test',
            'data_compromised' => 'Test',
            'data_categories_affected' => [DataCategory::NAME->value],
            'estimated_affected_subjects' => 100,
            'notification_required' => NotificationRequired::BOTH->value,
            'notification_status' => 'pending',
            'authority_notified' => false,
            'subjects_notified' => true,
            'immediate_actions' => 'Test',
            'mitigation_measures' => 'Test',
            'preventive_measures' => 'Test',
            'status' => Status::DETECTED->value,
            'affected_systems' => ['CRM'],
            'third_party_involved' => false,
        ];

        $response = $this->postJson('/api/privacy-incidents', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['subject_notification_date', 'notification_method']);
    }

    /**
     * Test store with conditional validation for status resolved
     */
    public function test_store_requires_resolution_fields_when_status_resolved(): void
    {
        $data = [
            'incident_title' => 'Test Incident',
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'is_breach' => false,
            'detected_date' => now()->toDateString(),
            'incident_description' => 'Test',
            'what_happened' => 'Test',
            'how_discovered' => 'Test',
            'data_compromised' => 'Test',
            'data_categories_affected' => [DataCategory::NAME->value],
            'estimated_affected_subjects' => 100,
            'notification_required' => NotificationRequired::BOTH->value,
            'notification_status' => 'pending',
            'authority_notified' => false,
            'subjects_notified' => false,
            'immediate_actions' => 'Test',
            'mitigation_measures' => 'Test',
            'preventive_measures' => 'Test',
            'status' => Status::RESOLVED->value,
            'affected_systems' => ['CRM'],
            'third_party_involved' => false,
        ];

        $response = $this->postJson('/api/privacy-incidents', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['root_cause_analysis', 'lessons_learned', 'resolution_date']);
    }

    /**
     * Test store with conditional validation for third_party_involved
     */
    public function test_store_requires_vendor_when_third_party_involved(): void
    {
        $data = [
            'incident_title' => 'Test Incident',
            'incident_type' => IncidentType::DATA_EXPOSURE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'is_breach' => false,
            'detected_date' => now()->toDateString(),
            'incident_description' => 'Test',
            'what_happened' => 'Test',
            'how_discovered' => 'Test',
            'data_compromised' => 'Test',
            'data_categories_affected' => [DataCategory::NAME->value],
            'estimated_affected_subjects' => 100,
            'notification_required' => NotificationRequired::BOTH->value,
            'notification_status' => 'pending',
            'authority_notified' => false,
            'subjects_notified' => false,
            'immediate_actions' => 'Test',
            'mitigation_measures' => 'Test',
            'preventive_measures' => 'Test',
            'status' => Status::DETECTED->value,
            'affected_systems' => ['CRM'],
            'third_party_involved' => true,
        ];

        $response = $this->postJson('/api/privacy-incidents', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vendor_id']);
    }

    /**
     * Test show retrieves privacy incident
     */
    public function test_show_retrieves_privacy_incident(): void
    {
        $incident = PrivacyIncident::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->getJson("/api/privacy-incidents/{$incident->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('message', 'Privacy incident retrieved successfully');
        $response->assertJsonPath('data.id', $incident->id);
        $response->assertJsonPath('data.incident_title', $incident->incident_title);
    }

    /**
     * Test show with non-existent incident
     */
    public function test_show_with_non_existent_incident(): void
    {
        $response = $this->getJson('/api/privacy-incidents/999999');

        $response->assertStatus(404);
    }

    /**
     * Test update modifies privacy incident
     */
    public function test_update_modifies_privacy_incident(): void
    {
        $incident = PrivacyIncident::factory()->create([
            'organization_id' => $this->organization->id,
            'incident_title' => 'Original Title',
            'status' => Status::DETECTED->value,
        ]);

        $data = [
            'incident_title' => 'Updated Title',
            'status' => Status::UNDER_INVESTIGATION->value,
        ];

        $response = $this->postJson("/api/privacy-incidents/{$incident->id}", $data);

        $response->assertStatus(200);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('message', 'Privacy incident updated successfully');
        $response->assertJsonPath('data.incident_title', 'Updated Title');
        $response->assertJsonPath('data.status', Status::UNDER_INVESTIGATION->value);

        $this->assertDatabaseHas('privacy_incidents', [
            'id' => $incident->id,
            'incident_title' => 'Updated Title',
            'status' => Status::UNDER_INVESTIGATION->value,
        ]);
    }

    /**
     * Test update with partial data
     */
    public function test_update_with_partial_data(): void
    {
        $originalDescription = 'Original Description';
        $incident = PrivacyIncident::factory()->create([
            'organization_id' => $this->organization->id,
            'incident_description' => $originalDescription,
            'status' => Status::DETECTED->value,
        ]);

        $data = [
            'status' => Status::CONTAINED->value,
        ];

        $response = $this->postJson("/api/privacy-incidents/{$incident->id}", $data);

        $response->assertStatus(200);
        $this->assertEquals($originalDescription, $response->json('data.incident_description'));
        $this->assertEquals(Status::CONTAINED->value, $response->json('data.status'));
    }

    /**
     * Test update sets updated_by
     */
    public function test_update_sets_updated_by(): void
    {
        $incident = PrivacyIncident::factory()->create(['organization_id' => $this->organization->id]);

        $data = ['status' => Status::RESOLVED->value];

        $response = $this->postJson("/api/privacy-incidents/{$incident->id}", $data);

        $response->assertStatus(200);
        $response->assertJsonPath('data.updated_by', $this->user->id);
    }

    /**
     * Test update recalculates notification_deadline
     */
    public function test_update_recalculates_notification_deadline(): void
    {
        $originalDate = now()->subDays(10);
        $incident = PrivacyIncident::factory()->create([
            'organization_id' => $this->organization->id,
            'detected_date' => $originalDate,
        ]);

        $newDate = now();

        $data = ['detected_date' => $newDate->toDateString()];

        $response = $this->postJson("/api/privacy-incidents/{$incident->id}", $data);

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.notification_deadline'));
    }

    /**
     * Test update with validation errors
     */
    public function test_update_with_validation_errors(): void
    {
        $incident = PrivacyIncident::factory()->create(['organization_id' => $this->organization->id]);

        $data = [
            'incident_type' => 'invalid_type',
            'status' => Status::RESOLVED->value,
        ];

        $response = $this->postJson("/api/privacy-incidents/{$incident->id}", $data);

        $response->assertStatus(422);
    }

    /**
     * Test destroy deletes privacy incident
     */
    public function test_destroy_deletes_privacy_incident(): void
    {
        $incident = PrivacyIncident::factory()->create(['organization_id' => $this->organization->id]);
        $id = $incident->id;

        $response = $this->deleteJson("/api/privacy-incidents/{$incident->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('message', 'Privacy incident deleted successfully');
        $response->assertJsonPath('data', null);

        $this->assertNull(PrivacyIncident::find($id));
    }

    /**
     * Test destroy with non-existent incident
     */
    public function test_destroy_with_non_existent_incident(): void
    {
        $response = $this->deleteJson('/api/privacy-incidents/999999');

        $response->assertStatus(404);
    }

    /**
     * Test response structure contains all required fields
     */
    public function test_response_structure_contains_all_required_fields(): void
    {
        $incident = PrivacyIncident::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->getJson("/api/privacy-incidents/{$incident->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'organization_id',
                'incident_code',
                'incident_title',
                'incident_type',
                'risk_level',
                'is_breach',
                'detected_date',
                'status',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    /**
     * Test incident belongs to authenticated user's organization
     */
    public function test_incident_is_associated_with_user_organization(): void
    {
        $incident = PrivacyIncident::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->getJson("/api/privacy-incidents/{$incident->id}");

        $response->assertStatus(200);
        $this->assertEquals($this->organization->id, $response->json('data.organization_id'));
    }
}

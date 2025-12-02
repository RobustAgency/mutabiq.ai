<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\KriIndicator;
use App\Models\Organization;
use App\Models\AiRiskRegister;
use App\Enums\KriIndicator\Status;
use App\Enums\KriIndicator\Frequency;
use App\Enums\KriIndicator\AlertRouting;
use App\Enums\KriIndicator\ActionOnBreach;
use App\Enums\KriIndicator\Directionality;
use App\Enums\KriIndicator\CollectionMethod;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KriIndicatorControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    /**
     * Test index returns only organization's KRI indicators
     */
    public function test_index_returns_only_organization_indicators(): void
    {
        KriIndicator::factory()->count(5)->create(['organization_id' => $this->organization->id]);
        KriIndicator::factory()->count(3)->create(); // other org

        $response = $this->actingAs($this->user)->getJson('/api/kri-indicators');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['data', 'total', 'current_page'], 'message', 'error']);

        $this->assertEquals(5, $response->json('data.total'));
    }

    /**
     * Test index with pagination
     */
    public function test_index_with_pagination(): void
    {
        KriIndicator::factory()->count(25)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/kri-indicators?per_page=10');

        $response->assertOk();
        $this->assertEquals(25, $response->json('data.total'));
        $this->assertCount(10, $response->json('data.data'));
    }

    /**
     * Test index filters by name
     */
    public function test_index_filters_by_name(): void
    {
        KriIndicator::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Model Accuracy Indicator',
        ]);
        KriIndicator::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Data Quality Score',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/kri-indicators?name=Accuracy');

        $response->assertOk();
        $this->assertEquals(1, $response->json('data.total'));
        $this->assertStringContainsString('Accuracy', $response->json('data.data.0.name'));
    }

    /**
     * Test index filters by status
     */
    public function test_index_filters_by_status(): void
    {
        KriIndicator::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'status' => Status::ACTIVE->value,
        ]);
        KriIndicator::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'status' => Status::PAUSED->value,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/kri-indicators?status=active');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.total'));
    }

    /**
     * Test index filters by frequency
     */
    public function test_index_filters_by_frequency(): void
    {
        KriIndicator::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'frequency' => Frequency::HOURLY->value,
        ]);
        KriIndicator::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'frequency' => Frequency::DAILY->value,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/kri-indicators?frequency=hourly');

        $response->assertOk();
        $this->assertEquals(2, $response->json('data.total'));
    }

    /**
     * Test index filters by directionality
     */
    public function test_index_filters_by_directionality(): void
    {
        KriIndicator::factory()->count(4)->create([
            'organization_id' => $this->organization->id,
            'directionality' => Directionality::HIGHER_IS_RISKIER->value,
        ]);
        KriIndicator::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'directionality' => Directionality::LOWER_IS_RISKIER->value,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/kri-indicators?directionality=higher_is_riskier');

        $response->assertOk();
        $this->assertEquals(4, $response->json('data.total'));
    }

    /**
     * Test index filters by collection method
     */
    public function test_index_filters_by_collection_method(): void
    {
        KriIndicator::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'collection_method' => CollectionMethod::SCHEDULED_QUERY->value,
        ]);
        KriIndicator::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'collection_method' => CollectionMethod::MANUAL_ENTRY->value,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/kri-indicators?collection_method=scheduled_query');

        $response->assertOk();
        $this->assertEquals(3, $response->json('data.total'));
    }

    /**
     * Test index filters by action on breach
     */
    public function test_index_filters_by_action_on_breach(): void
    {
        KriIndicator::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'action_on_breach' => ActionOnBreach::NOTIFY_ONLY->value,
        ]);
        KriIndicator::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'action_on_breach' => ActionOnBreach::OPEN_INCIDENT->value,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/kri-indicators?action_on_breach=notify_only');

        $response->assertOk();
        $this->assertEquals(2, $response->json('data.total'));
    }

    /**
     * Test store creates KRI indicator with valid data
     */
    public function test_store_creates_indicator_with_valid_data(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create(['organization_id' => $this->organization->id]);

        $payload = [
            'ai_risk_register_id' => $aiRiskRegister->id,
            'name' => 'Test KRI Indicator',
            'definition' => 'Test definition for KRI',
            'directionality' => Directionality::HIGHER_IS_RISKIER->value,
            'unit' => 'percentage',
            'sample_window' => 'daily',
            'threshold_warning' => 75,
            'threshold_critical' => 90,
            'data_source' => 'database',
            'collection_method' => CollectionMethod::SCHEDULED_QUERY->value,
            'frequency' => Frequency::DAILY->value,
            'alert_routing' => AlertRouting::RISK_TEAM->value,
            'action_on_breach' => ActionOnBreach::NOTIFY_ONLY->value,
            'status' => Status::ACTIVE->value,
            'owner_team' => 'Risk Team',
            'notes' => 'Test notes',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/kri-indicators', $payload);

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'organization_id', 'name', 'status'], 'message', 'error']);

        $this->assertDatabaseHas('kri_indicators', [
            'organization_id' => $this->organization->id,
            'ai_risk_register_id' => $aiRiskRegister->id,
            'name' => 'Test KRI Indicator',
            'status' => Status::ACTIVE->value,
            'created_by' => $this->user->id,
        ]);
    }

    /**
     * Test store sets created_by to authenticated user
     */
    public function test_store_sets_created_by_to_authenticated_user(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create(['organization_id' => $this->organization->id]);

        $payload = [
            'ai_risk_register_id' => $aiRiskRegister->id,
            'name' => 'Test Indicator',
            'definition' => 'Test definition',
            'directionality' => Directionality::HIGHER_IS_RISKIER->value,
            'unit' => 'percentage',
            'sample_window' => 'daily',
            'threshold_warning' => 75,
            'threshold_critical' => 90,
            'data_source' => 'database',
            'collection_method' => CollectionMethod::SCHEDULED_QUERY->value,
            'frequency' => Frequency::DAILY->value,
            'alert_routing' => AlertRouting::RISK_TEAM->value,
            'action_on_breach' => ActionOnBreach::NOTIFY_ONLY->value,
            'status' => Status::ACTIVE->value,
            'owner_team' => 'Risk Team',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/kri-indicators', $payload);

        $response->assertOk();
        $this->assertEquals($this->user->id, $response->json('data.created_by'));
    }

    /**
     * Test store validation fails for missing required fields
     */
    public function test_store_validation_fails_for_missing_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/kri-indicators', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'ai_risk_register_id',
                'name',
                'definition',
                'directionality',
                'sample_window',
                'threshold_warning',
                'threshold_critical',
                'data_source',
                'collection_method',
                'frequency',
                'alert_routing',
                'action_on_breach',
                'status',
                'owner_team',
            ]);
    }

    /**
     * Test store validation fails for invalid status
     */
    public function test_store_validation_fails_for_invalid_status(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create(['organization_id' => $this->organization->id]);

        $payload = [
            'ai_risk_register_id' => $aiRiskRegister->id,
            'name' => 'Test Indicator',
            'definition' => 'Test definition',
            'directionality' => Directionality::HIGHER_IS_RISKIER->value,
            'unit' => 'percentage',
            'sample_window' => 'daily',
            'threshold_warning' => 75,
            'threshold_critical' => 90,
            'data_source' => 'database',
            'collection_method' => CollectionMethod::SCHEDULED_QUERY->value,
            'frequency' => Frequency::DAILY->value,
            'alert_routing' => AlertRouting::RISK_TEAM->value,
            'action_on_breach' => ActionOnBreach::NOTIFY_ONLY->value,
            'status' => 'invalid_status',
            'owner_team' => 'Risk Team',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/kri-indicators', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * Test store validation fails for invalid frequency
     */
    public function test_store_validation_fails_for_invalid_frequency(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create(['organization_id' => $this->organization->id]);

        $payload = [
            'ai_risk_register_id' => $aiRiskRegister->id,
            'name' => 'Test Indicator',
            'definition' => 'Test definition',
            'directionality' => Directionality::HIGHER_IS_RISKIER->value,
            'unit' => 'percentage',
            'sample_window' => 'daily',
            'threshold_warning' => 75,
            'threshold_critical' => 90,
            'data_source' => 'database',
            'collection_method' => CollectionMethod::SCHEDULED_QUERY->value,
            'frequency' => 'invalid_frequency',
            'alert_routing' => AlertRouting::RISK_TEAM->value,
            'action_on_breach' => ActionOnBreach::NOTIFY_ONLY->value,
            'status' => Status::ACTIVE->value,
            'owner_team' => 'Risk Team',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/kri-indicators', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['frequency']);
    }

    /**
     * Test store validation fails for non-existent AI risk register
     */
    public function test_store_validation_fails_for_non_existent_ai_risk_register(): void
    {
        $payload = [
            'ai_risk_register_id' => 9999,
            'name' => 'Test Indicator',
            'definition' => 'Test definition',
            'directionality' => Directionality::HIGHER_IS_RISKIER->value,
            'unit' => 'percentage',
            'sample_window' => 'daily',
            'threshold_warning' => 75,
            'threshold_critical' => 90,
            'data_source' => 'database',
            'collection_method' => CollectionMethod::SCHEDULED_QUERY->value,
            'frequency' => Frequency::DAILY->value,
            'alert_routing' => AlertRouting::RISK_TEAM->value,
            'action_on_breach' => ActionOnBreach::NOTIFY_ONLY->value,
            'status' => Status::ACTIVE->value,
            'owner_team' => 'Risk Team',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/kri-indicators', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ai_risk_register_id']);
    }

    /**
     * Test show returns indicator details
     */
    public function test_show_returns_indicator_details(): void
    {
        $indicator = KriIndicator::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson("/api/kri-indicators/{$indicator->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $indicator->id)
            ->assertJsonPath('data.name', $indicator->name)
            ->assertJsonPath('data.status', $indicator->status);
    }

    /**
     * Test show loads relationships
     */
    public function test_show_loads_relationships(): void
    {
        $indicator = KriIndicator::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson("/api/kri-indicators/{$indicator->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'organization', 'ai_risk_register', 'created_by']]);
    }

    /**
     * Test update modifies indicator
     */
    public function test_update_modifies_indicator(): void
    {
        $indicator = KriIndicator::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => Status::DRAFT->value,
        ]);

        $payload = [
            'ai_risk_register_id' => $indicator->ai_risk_register_id,
            'name' => 'Updated Name',
            'definition' => $indicator->definition,
            'directionality' => $indicator->directionality,
            'unit' => $indicator->unit,
            'sample_window' => $indicator->sample_window,
            'threshold_warning' => 80,
            'threshold_critical' => 95,
            'data_source' => $indicator->data_source,
            'collection_method' => $indicator->collection_method,
            'frequency' => $indicator->frequency,
            'alert_routing' => is_array($indicator->alert_routing) ? $indicator->alert_routing[0] : $indicator->alert_routing,
            'action_on_breach' => $indicator->action_on_breach,
            'status' => Status::ACTIVE->value,
            'owner_team' => 'Updated Team',
            'notes' => 'Updated notes',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/kri-indicators/{$indicator->id}", $payload);

        $response->assertOk();
        $this->assertDatabaseHas('kri_indicators', [
            'id' => $indicator->id,
            'name' => 'Updated Name',
            'status' => Status::ACTIVE->value,
            'threshold_warning' => 80,
        ]);
    }

    /**
     * Test update validation fails for invalid data
     */
    public function test_update_validation_fails_for_invalid_data(): void
    {
        $indicator = KriIndicator::factory()->create(['organization_id' => $this->organization->id]);

        $payload = [
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/kri-indicators/{$indicator->id}", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * Test destroy deletes indicator
     */
    public function test_destroy_deletes_indicator(): void
    {
        $indicator = KriIndicator::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/kri-indicators/{$indicator->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'KRI Indicator deleted successfully');

        $this->assertDatabaseMissing('kri_indicators', ['id' => $indicator->id]);
    }

    /**
     * Test destroy does not affect other indicators
     */
    public function test_destroy_does_not_affect_other_indicators(): void
    {
        $indicator1 = KriIndicator::factory()->create(['organization_id' => $this->organization->id]);
        $indicator2 = KriIndicator::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/kri-indicators/{$indicator1->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('kri_indicators', ['id' => $indicator1->id]);
        $this->assertDatabaseHas('kri_indicators', ['id' => $indicator2->id]);
    }

    /**
     * Test unauthenticated user cannot access endpoints
     */
    public function test_unauthenticated_cannot_access_endpoints(): void
    {
        $indicator = KriIndicator::factory()->create();

        $this->getJson('/api/kri-indicators')->assertUnauthorized();
        $this->postJson('/api/kri-indicators', [])->assertUnauthorized();
        $this->getJson("/api/kri-indicators/{$indicator->id}")->assertUnauthorized();
        $this->postJson("/api/kri-indicators/{$indicator->id}", [])->assertUnauthorized();
        $this->deleteJson("/api/kri-indicators/{$indicator->id}")->assertUnauthorized();
    }

    /**
     * Test response structure for index
     */
    public function test_index_response_structure(): void
    {
        KriIndicator::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/kri-indicators');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'status',
                            'frequency',
                            'directionality',
                        ],
                    ],
                    'total',
                    'current_page',
                    'per_page',
                    'last_page',
                ],
                'message',
                'error',
            ]);
    }

    /**
     * Test response structure for store
     */
    public function test_store_response_structure(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create(['organization_id' => $this->organization->id]);

        $payload = [
            'ai_risk_register_id' => $aiRiskRegister->id,
            'name' => 'Test Indicator',
            'definition' => 'Test definition',
            'directionality' => Directionality::HIGHER_IS_RISKIER->value,
            'unit' => 'percentage',
            'sample_window' => 'daily',
            'threshold_warning' => 75,
            'threshold_critical' => 90,
            'data_source' => 'database',
            'collection_method' => CollectionMethod::SCHEDULED_QUERY->value,
            'frequency' => Frequency::DAILY->value,
            'alert_routing' => AlertRouting::RISK_TEAM->value,
            'action_on_breach' => ActionOnBreach::NOTIFY_ONLY->value,
            'status' => Status::ACTIVE->value,
            'owner_team' => 'Risk Team',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/kri-indicators', $payload);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'organization_id',
                    'ai_risk_register_id',
                    'name',
                    'definition',
                    'status',
                    'frequency',
                    'created_at',
                    'updated_at',
                ],
                'message',
                'error',
            ]);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\RecordOfProcessingActivity;
use App\Models\DataProtectionImpactAssessment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\DataProtectionImpactAssessment\Stage;
use App\Enums\DataProtectionImpactAssessment\Status;
use App\Enums\DataProtectionImpactAssessment\RiskLevel;
use App\Enums\DataProtectionImpactAssessment\Jurisdiction;
use App\Enums\DataProtectionImpactAssessment\LinkedAssetsType;

class DataProtectionImpactAssessmentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $bearerToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->bearerToken = 'Bearer test-token';
    }

    /**
     * Test listing DPIAs with default pagination
     */
    public function test_index_returns_paginated_dpias(): void
    {
        $dpias = DataProtectionImpactAssessment::factory(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/data-protection-impact-assessments');

        $response->assertStatus(200);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('message', 'Data Protection Impact Assessments retrieved successfully');
    }

    /**
     * Test listing DPIAs with status filter
     */
    public function test_index_filters_by_status(): void
    {
        DataProtectionImpactAssessment::factory(5)->create(['status' => Status::DRAFT->value]);
        DataProtectionImpactAssessment::factory(3)->create(['status' => Status::COMPLETED->value]);

        $response = $this->actingAs($this->user)->getJson('/api/data-protection-impact-assessments?status=completed');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data.data');
    }

    /**
     * Test listing DPIAs with stage filter
     */
    public function test_index_filters_by_stage(): void
    {
        DataProtectionImpactAssessment::factory(4)->create(['stage' => Stage::NECESSITY->value]);
        DataProtectionImpactAssessment::factory(2)->create(['stage' => Stage::APPROVAL->value]);

        $response = $this->actingAs($this->user)->getJson('/api/data-protection-impact-assessments?stage=approval');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
    }

    /**
     * Test listing DPIAs with risk_level filter
     */
    public function test_index_filters_by_risk_level(): void
    {
        DataProtectionImpactAssessment::factory(3)->create(['risk_level' => RiskLevel::LOW->value]);
        DataProtectionImpactAssessment::factory(2)->create(['risk_level' => RiskLevel::HIGH->value]);

        $response = $this->actingAs($this->user)->getJson('/api/data-protection-impact-assessments?risk_level=high');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
    }

    /**
     * Test listing DPIAs with name search
     */
    public function test_index_searches_by_name(): void
    {
        DataProtectionImpactAssessment::factory()->create(['dpia_name' => 'Customer Data Analysis']);
        DataProtectionImpactAssessment::factory()->create(['dpia_name' => 'Employee Records']);

        $response = $this->actingAs($this->user)->getJson('/api/data-protection-impact-assessments?name=Customer');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    /**
     * Test listing DPIAs with pagination
     */
    public function test_index_paginates_correctly(): void
    {
        DataProtectionImpactAssessment::factory(25)->create();

        $response = $this->actingAs($this->user)->getJson('/api/data-protection-impact-assessments?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data.data');
        $response->assertJsonPath('data.per_page', 10);
        $response->assertJsonPath('data.total', 25);
    }

    /**
     * Test creating a DPIA with all required fields
     */
    public function test_store_creates_dpia_with_all_fields(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();

        $data = [
            'dpia_name' => 'Customer Data Analysis',
            'ropa_id' => $ropa->id,
            'linked_asset_type' => LinkedAssetsType::AI_MODEL->value,
            'automated_trigger' => true,
            'trigger_reason' => 'High risk processing',
            'risk_level' => RiskLevel::HIGH->value,
            'risk_score' => 18,
            'stage' => Stage::NECESSITY->value,
            'completion_percentage' => 50,
            'necessity_justification' => 'Processing is necessary for business operations',
            'proportionality_assessment' => 'Processing is proportionate',
            'alternatives_considered' => 'Several alternatives were considered',
            'likelihood_assessment' => 'Low likelihood of breach',
            'impact_assessment' => 'High impact if breach occurs',
            'data_subjects_consulted' => false,
            'status' => Status::DRAFT->value,
            'review_frequency_months' => 12,
            'applicable_jurisdictions' => [Jurisdiction::EU->value, Jurisdiction::UK->value],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-protection-impact-assessments', $data);

        $response->assertStatus(201);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('message', 'Data Protection Impact Assessment created successfully');

        $dpia = DataProtectionImpactAssessment::where('dpia_name', 'Customer Data Analysis')->first();
        $this->assertNotNull($dpia);
        $this->assertEquals($ropa->id, $dpia->ropa_id);
        $this->assertEquals(RiskLevel::HIGH->value, $dpia->risk_level);
        $this->assertEquals(18, $dpia->risk_score);
        $this->assertEquals(Status::DRAFT->value, $dpia->status);
        $this->assertEquals(Stage::NECESSITY->value, $dpia->stage);
    }

    /**
     * Test creating a DPIA auto-generates dpia_code
     */
    public function test_store_auto_generates_dpia_code(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();

        $data = [
            'dpia_name' => 'Test DPIA',
            'ropa_id' => $ropa->id,
            'linked_asset_type' => LinkedAssetsType::SYSTEM->value,
            'automated_trigger' => false,
            'trigger_reason' => 'Manual trigger',
            'risk_level' => RiskLevel::LOW->value,
            'risk_score' => 5,
            'stage' => Stage::SCREENING->value,
            'completion_percentage' => 50,
            'proportionality_assessment' => 'Proportionate',
            'alternatives_considered' => 'Alternatives considered',
            'likelihood_assessment' => 'Low likelihood',
            'impact_assessment' => 'Low impact',
            'data_subjects_consulted' => false,
            'status' => Status::DRAFT->value,
            'review_frequency_months' => 6,
            'applicable_jurisdictions' => [Jurisdiction::EU->value],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-protection-impact-assessments', $data);

        $response->assertStatus(201);

        $dpia = DataProtectionImpactAssessment::where('dpia_name', 'Test DPIA')->first();
        $this->assertNotNull($dpia->dpia_code);
        $this->assertStringStartsWith('DPIA-'.date('Y'), $dpia->dpia_code);
    }

    /**
     * Test creating a DPIA sets created_by and updated_by
     */
    public function test_store_sets_created_by_and_updated_by(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();

        $data = [
            'dpia_name' => 'Test DPIA',
            'ropa_id' => $ropa->id,
            'linked_asset_type' => LinkedAssetsType::SYSTEM->value,
            'automated_trigger' => false,
            'trigger_reason' => 'Manual trigger',
            'risk_level' => RiskLevel::LOW->value,
            'risk_score' => 5,
            'completion_percentage' => 50,
            'stage' => Stage::SCREENING->value,
            'proportionality_assessment' => 'Proportionate',
            'alternatives_considered' => 'Alternatives considered',
            'likelihood_assessment' => 'Low likelihood',
            'impact_assessment' => 'Low impact',
            'data_subjects_consulted' => false,
            'status' => Status::DRAFT->value,
            'review_frequency_months' => 6,
            'applicable_jurisdictions' => [Jurisdiction::EU->value],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-protection-impact-assessments', $data);

        $response->assertStatus(201);

        $dpia = DataProtectionImpactAssessment::where('dpia_name', 'Test DPIA')->first();
        $this->assertEquals($this->user->id, $dpia->created_by);
        $this->assertEquals($this->user->id, $dpia->updated_by);
    }

    /**
     * Test creating a DPIA calculates next_review_date
     */
    public function test_store_calculates_next_review_date(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();

        $data = [
            'dpia_name' => 'Test DPIA',
            'ropa_id' => $ropa->id,
            'linked_asset_type' => LinkedAssetsType::SYSTEM->value,
            'automated_trigger' => false,
            'trigger_reason' => 'Manual trigger',
            'risk_level' => RiskLevel::LOW->value,
            'risk_score' => 5,
            'completion_percentage' => 50,
            'stage' => Stage::SCREENING->value,
            'proportionality_assessment' => 'Proportionate',
            'alternatives_considered' => 'Alternatives considered',
            'likelihood_assessment' => 'Low likelihood',
            'impact_assessment' => 'Low impact',
            'data_subjects_consulted' => false,
            'status' => Status::DRAFT->value,
            'review_frequency_months' => 12,
            'applicable_jurisdictions' => [Jurisdiction::EU->value],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-protection-impact-assessments', $data);

        $response->assertStatus(201);

        $dpia = DataProtectionImpactAssessment::where('dpia_name', 'Test DPIA')->first();
        $this->assertNotNull($dpia->next_review_date);
        $this->assertTrue($dpia->next_review_date->isAfter(now()));
    }

    /**
     * Test creating a DPIA with validation errors
     */
    public function test_store_validates_required_fields(): void
    {
        $data = [
            'dpia_name' => 'Incomplete DPIA',
            // Missing required fields
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-protection-impact-assessments', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ropa_id', 'linked_asset_type', 'risk_level', 'stage']);
    }

    /**
     * Test creating a DPIA with invalid enum values
     */
    public function test_store_validates_enum_values(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();

        $data = [
            'dpia_name' => 'Test DPIA',
            'ropa_id' => $ropa->id,
            'linked_asset_type' => 'invalid_type',
            'automated_trigger' => false,
            'trigger_reason' => 'Manual trigger',
            'risk_level' => 'invalid_level',
            'risk_score' => 5,
            'stage' => 'invalid_stage',
            'proportionality_assessment' => 'Proportionate',
            'alternatives_considered' => 'Alternatives considered',
            'likelihood_assessment' => 'Low likelihood',
            'impact_assessment' => 'Low impact',
            'data_subjects_consulted' => false,
            'status' => Status::DRAFT->value,
            'review_frequency_months' => 6,
            'applicable_jurisdictions' => [Jurisdiction::EU->value],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-protection-impact-assessments', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['linked_asset_type', 'risk_level', 'stage']);
    }

    /**
     * Test showing a DPIA
     */
    public function test_show_returns_dpia(): void
    {
        $dpia = DataProtectionImpactAssessment::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/data-protection-impact-assessments/{$dpia->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('data.id', $dpia->id);
        $response->assertJsonPath('data.dpia_name', $dpia->dpia_name);
    }

    /**
     * Test showing a non-existent DPIA returns 404
     */
    public function test_show_returns_404_for_non_existent_dpia(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/data-protection-impact-assessments/99999');

        $response->assertStatus(404);
    }

    /**
     * Test updating a DPIA
     */
    public function test_update_modifies_dpia(): void
    {
        $dpia = DataProtectionImpactAssessment::factory()->create([
            'status' => Status::DRAFT->value,
            'risk_level' => RiskLevel::LOW->value,
        ]);

        $data = [
            'status' => Status::IN_PROGRESS->value,
            'risk_level' => RiskLevel::MEDIUM->value,
            'completion_percentage' => 50,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/data-protection-impact-assessments/{$dpia->id}", $data);

        $response->assertStatus(200);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('message', 'Data Protection Impact Assessment updated successfully');

        $updated = $dpia->fresh();
        $this->assertEquals(Status::IN_PROGRESS->value, $updated->status);
        $this->assertEquals(RiskLevel::MEDIUM->value, $updated->risk_level);
        $this->assertEquals(50, $updated->completion_percentage);
    }

    /**
     * Test updating a DPIA sets updated_by
     */
    public function test_update_sets_updated_by(): void
    {
        $dpia = DataProtectionImpactAssessment::factory()->create();
        $originalUpdatedBy = $dpia->updated_by;

        $data = [
            'status' => Status::IN_PROGRESS->value,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/data-protection-impact-assessments/{$dpia->id}", $data);

        $response->assertStatus(200);

        $updated = $dpia->fresh();
        $this->assertEquals($this->user->id, $updated->updated_by);
    }

    /**
     * Test updating a DPIA with partial data
     */
    public function test_update_with_partial_data(): void
    {
        $dpia = DataProtectionImpactAssessment::factory()->create([
            'status' => Status::DRAFT->value,
            'risk_level' => RiskLevel::LOW->value,
            'stage' => Stage::NECESSITY->value,
        ]);

        $data = ['status' => Status::COMPLETED->value];

        $response = $this->actingAs($this->user)->postJson("/api/data-protection-impact-assessments/{$dpia->id}", $data);

        $response->assertStatus(200);

        $updated = $dpia->fresh();
        $this->assertEquals(Status::COMPLETED->value, $updated->status);
        $this->assertEquals(RiskLevel::LOW->value, $updated->risk_level);
        $this->assertEquals(Stage::NECESSITY->value, $updated->stage);
    }

    /**
     * Test updating a DPIA calculates next_review_date
     */
    public function test_update_calculates_next_review_date(): void
    {
        $dpia = DataProtectionImpactAssessment::factory()->create();

        $data = [
            'review_frequency_months' => 24,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/data-protection-impact-assessments/{$dpia->id}", $data);

        $response->assertStatus(200);

        $updated = $dpia->fresh();
        $this->assertNotNull($updated->next_review_date);
        $this->assertTrue($updated->next_review_date->isAfter(now()));
    }

    /**
     * Test updating a DPIA with validation errors
     */
    public function test_update_validates_enum_values(): void
    {
        $dpia = DataProtectionImpactAssessment::factory()->create();

        $data = [
            'status' => 'invalid_status',
            'risk_level' => 'invalid_risk',
        ];

        $response = $this->actingAs($this->user)->postJson("/api/data-protection-impact-assessments/{$dpia->id}", $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status', 'risk_level']);
    }

    /**
     * Test deleting a DPIA
     */
    public function test_destroy_deletes_dpia(): void
    {
        $dpia = DataProtectionImpactAssessment::factory()->create();
        $id = $dpia->id;

        $response = $this->actingAs($this->user)->deleteJson("/api/data-protection-impact-assessments/{$dpia->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('error', false);
        $response->assertJsonPath('message', 'Data Protection Impact Assessment deleted successfully');
        $response->assertJsonPath('data', null);

        $this->assertDatabaseMissing('data_protection_impact_assessments', ['id' => $id]);
    }

    /**
     * Test deleting a non-existent DPIA returns 404
     */
    public function test_destroy_returns_404_for_non_existent_dpia(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/data-protection-impact-assessments/99999');

        $response->assertStatus(404);
    }

    /**
     * Test store with all enum values for linked_asset_type
     */
    public function test_store_accepts_all_linked_asset_types(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();

        foreach (LinkedAssetsType::cases() as $type) {
            $data = [
                'dpia_name' => 'Test DPIA '.$type->value,
                'ropa_id' => $ropa->id,
                'linked_asset_type' => $type->value,
                'automated_trigger' => false,
                'trigger_reason' => 'Manual trigger',
                'risk_level' => RiskLevel::LOW->value,
                'risk_score' => 5,
                'completion_percentage' => 50,
                'stage' => Stage::SCREENING->value,
                'proportionality_assessment' => 'Proportionate',
                'alternatives_considered' => 'Alternatives considered',
                'likelihood_assessment' => 'Low likelihood',
                'impact_assessment' => 'Low impact',
                'data_subjects_consulted' => false,
                'status' => Status::DRAFT->value,
                'review_frequency_months' => 6,
                'applicable_jurisdictions' => [Jurisdiction::EU->value],
            ];

            $response = $this->actingAs($this->user)->postJson('/api/data-protection-impact-assessments', $data);

            $response->assertStatus(201);
            $this->assertDatabaseHas('data_protection_impact_assessments', [
                'linked_asset_type' => $type->value,
            ]);
        }
    }

    /**
     * Test store with all risk levels
     */
    public function test_store_accepts_all_risk_levels(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();

        foreach (RiskLevel::cases() as $level) {
            $data = [
                'dpia_name' => 'Test DPIA '.$level->value,
                'ropa_id' => $ropa->id,
                'linked_asset_type' => LinkedAssetsType::SYSTEM->value,
                'automated_trigger' => false,
                'trigger_reason' => 'Manual trigger',
                'risk_level' => $level->value,
                'completion_percentage' => 50,
                'risk_score' => 5,
                'stage' => Stage::SCREENING->value,
                'proportionality_assessment' => 'Proportionate',
                'alternatives_considered' => 'Alternatives considered',
                'likelihood_assessment' => 'Low likelihood',
                'impact_assessment' => 'Low impact',
                'data_subjects_consulted' => false,
                'status' => Status::DRAFT->value,
                'review_frequency_months' => 6,
                'applicable_jurisdictions' => [Jurisdiction::EU->value],
            ];

            $response = $this->actingAs($this->user)->postJson('/api/data-protection-impact-assessments', $data);

            $response->assertStatus(201);
            $this->assertDatabaseHas('data_protection_impact_assessments', [
                'risk_level' => $level->value,
            ]);
        }
    }

    /**
     * Test store with multiple jurisdictions
     */
    public function test_store_saves_multiple_jurisdictions(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();

        $data = [
            'dpia_name' => 'Multi-jurisdiction DPIA',
            'ropa_id' => $ropa->id,
            'linked_asset_type' => LinkedAssetsType::SYSTEM->value,
            'automated_trigger' => false,
            'trigger_reason' => 'Manual trigger',
            'risk_level' => RiskLevel::LOW->value,
            'risk_score' => 5,
            'completion_percentage' => 50,
            'stage' => Stage::SCREENING->value,
            'proportionality_assessment' => 'Proportionate',
            'alternatives_considered' => 'Alternatives considered',
            'likelihood_assessment' => 'Low likelihood',
            'impact_assessment' => 'Low impact',
            'data_subjects_consulted' => false,
            'status' => Status::DRAFT->value,
            'review_frequency_months' => 6,
            'applicable_jurisdictions' => [
                Jurisdiction::EU->value,
                Jurisdiction::UK->value,
                Jurisdiction::UAE->value,
            ],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-protection-impact-assessments', $data);

        $response->assertStatus(201);

        $dpia = DataProtectionImpactAssessment::where('dpia_name', 'Multi-jurisdiction DPIA')->first();
        $this->assertCount(3, $dpia->applicable_jurisdictions);
        $this->assertContains(Jurisdiction::EU->value, $dpia->applicable_jurisdictions);
        $this->assertContains(Jurisdiction::UK->value, $dpia->applicable_jurisdictions);
        $this->assertContains(Jurisdiction::UAE->value, $dpia->applicable_jurisdictions);
    }

    /**
     * Test store response includes all fields in resource
     */
    public function test_store_response_includes_all_fields(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();

        $data = [
            'dpia_name' => 'Complete DPIA',
            'ropa_id' => $ropa->id,
            'linked_asset_type' => LinkedAssetsType::AI_MODEL->value,
            'automated_trigger' => true,
            'trigger_reason' => 'High risk processing',
            'risk_level' => RiskLevel::HIGH->value,
            'risk_score' => 18,
            'stage' => Stage::NECESSITY->value,
            'completion_percentage' => 50,
            'necessity_justification' => 'Necessary for operations',
            'proportionality_assessment' => 'Processing is proportionate',
            'alternatives_considered' => 'Several alternatives were considered',
            'likelihood_assessment' => 'Low likelihood of breach',
            'impact_assessment' => 'High impact if breach occurs',
            'data_subjects_consulted' => false,
            'status' => Status::DRAFT->value,
            'review_frequency_months' => 12,
            'applicable_jurisdictions' => [Jurisdiction::EU->value],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-protection-impact-assessments', $data);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'id',
                'dpia_code',
                'dpia_name',
                'ropa_id',
                'risk_level',
                'risk_score',
                'stage',
                'status',
                'completion_percentage',
                'created_by',
                'updated_by',
            ],
        ]);
    }
}

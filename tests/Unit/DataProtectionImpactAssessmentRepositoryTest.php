<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\RecordOfProcessingActivity;
use App\Models\DataProtectionImpactAssessment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\DataProtectionImpactAssessment\Stage;
use App\Enums\DataProtectionImpactAssessment\Status;
use App\Enums\DataProtectionImpactAssessment\RiskLevel;
use App\Enums\DataProtectionImpactAssessment\Jurisdiction;
use App\Enums\DataProtectionImpactAssessment\LinkedAssetsType;
use App\Repositories\DataProtectionImpactAssessmentRepository;

class DataProtectionImpactAssessmentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private DataProtectionImpactAssessmentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(DataProtectionImpactAssessmentRepository::class);
    }

    /**
     * Test getting filtered DPIAs with default pagination
     */
    public function test_get_filtered_dpias_with_default_pagination(): void
    {
        DataProtectionImpactAssessment::factory(20)->create();

        $result = $this->repository->getFilteredDataProtectionImpactAssessments();

        $this->assertCount(15, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test getting filtered DPIAs with custom per_page
     */
    public function test_get_filtered_dpias_with_custom_per_page(): void
    {
        DataProtectionImpactAssessment::factory(30)->create();

        $result = $this->repository->getFilteredDataProtectionImpactAssessments(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertEquals(30, $result->total());
        $this->assertEquals(10, $result->perPage());
    }

    /**
     * Test filtering DPIAs by name
     */
    public function test_get_filtered_dpias_by_name(): void
    {
        DataProtectionImpactAssessment::factory(3)->create(['dpia_name' => 'Customer Data Processing']);
        DataProtectionImpactAssessment::factory(2)->create(['dpia_name' => 'Employee Records']);

        $result = $this->repository->getFilteredDataProtectionImpactAssessments(['name' => 'Customer']);

        $this->assertCount(3, $result->items());
        $this->assertTrue($result->items()[0]->dpia_name === 'Customer Data Processing');
    }

    /**
     * Test filtering DPIAs by status
     */
    public function test_get_filtered_dpias_by_status(): void
    {
        DataProtectionImpactAssessment::factory(5)->create(['status' => Status::DRAFT->value]);
        DataProtectionImpactAssessment::factory(3)->create(['status' => Status::COMPLETED->value]);

        $result = $this->repository->getFilteredDataProtectionImpactAssessments(['status' => Status::COMPLETED->value]);

        $this->assertCount(3, $result->items());
        $this->assertTrue($result->items()[0]->status === Status::COMPLETED->value);
    }

    /**
     * Test filtering DPIAs by stage
     */
    public function test_get_filtered_dpias_by_stage(): void
    {
        DataProtectionImpactAssessment::factory(4)->create(['stage' => Stage::NECESSITY->value]);
        DataProtectionImpactAssessment::factory(2)->create(['stage' => Stage::APPROVAL->value]);

        $result = $this->repository->getFilteredDataProtectionImpactAssessments(['stage' => Stage::APPROVAL->value]);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->items()[0]->stage === Stage::APPROVAL->value);
    }

    /**
     * Test filtering DPIAs by risk_level
     */
    public function test_get_filtered_dpias_by_risk_level(): void
    {
        DataProtectionImpactAssessment::factory(3)->create(['risk_level' => RiskLevel::LOW->value]);
        DataProtectionImpactAssessment::factory(2)->create(['risk_level' => RiskLevel::HIGH->value]);

        $result = $this->repository->getFilteredDataProtectionImpactAssessments(['risk_level' => RiskLevel::HIGH->value]);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->items()[0]->risk_level === RiskLevel::HIGH->value);
    }

    /**
     * Test filtering DPIAs with multiple criteria
     */
    public function test_get_filtered_dpias_with_multiple_filters(): void
    {
        DataProtectionImpactAssessment::factory(5)->create([
            'status' => Status::COMPLETED->value,
            'stage' => Stage::APPROVAL->value,
            'risk_level' => RiskLevel::HIGH->value,
        ]);
        DataProtectionImpactAssessment::factory(3)->create([
            'status' => Status::DRAFT->value,
            'stage' => Stage::NECESSITY->value,
            'risk_level' => RiskLevel::LOW->value,
        ]);

        $result = $this->repository->getFilteredDataProtectionImpactAssessments([
            'status' => Status::COMPLETED->value,
            'stage' => Stage::APPROVAL->value,
            'risk_level' => RiskLevel::HIGH->value,
        ]);

        $this->assertCount(5, $result->items());
    }

    /**
     * Test creating a DPIA
     */
    public function test_create_dpia(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();
        $data = [
            'dpia_code' => 'DPIA-2025-001',
            'dpia_name' => 'Customer Data Analysis',
            'ropa_id' => $ropa->id,
            'linked_asset_type' => LinkedAssetsType::AI_MODEL->value,
            'automated_trigger' => true,
            'trigger_reason' => 'High risk processing',
            'risk_level' => RiskLevel::HIGH->value,
            'risk_score' => 18,
            'stage' => Stage::NECESSITY->value,
            'completion_percentage' => 50,
            'proportionality_assessment' => 'Processing is proportionate',
            'alternatives_considered' => 'Several alternatives were considered',
            'likelihood_assessment' => 'Low likelihood of breach',
            'impact_assessment' => 'High impact if breach occurs',
            'data_subjects_consulted' => false,
            'status' => Status::DRAFT->value,
            'review_frequency_months' => 12,
            'applicable_jurisdictions' => [Jurisdiction::EU->value, Jurisdiction::UK->value],
            'created_by' => 1,
            'updated_by' => 1,
        ];

        $dpia = $this->repository->createDataProtectionImpactAssessment($data);

        $this->assertInstanceOf(DataProtectionImpactAssessment::class, $dpia);
        $this->assertEquals('DPIA-2025-001', $dpia->dpia_code);
        $this->assertEquals('Customer Data Analysis', $dpia->dpia_name);
        $this->assertEquals(RiskLevel::HIGH->value, $dpia->risk_level);
        $this->assertDatabaseHas('data_protection_impact_assessments', ['dpia_code' => 'DPIA-2025-001']);
    }

    /**
     * Test creating DPIA with minimal data
     */
    public function test_create_dpia_with_minimal_data(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();
        $data = [
            'dpia_code' => 'DPIA-2025-minimal',
            'dpia_name' => 'Minimal DPIA',
            'ropa_id' => $ropa->id,
            'linked_asset_type' => LinkedAssetsType::SYSTEM->value,
            'automated_trigger' => false,
            'trigger_reason' => 'Manual trigger',
            'risk_level' => RiskLevel::LOW->value,
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
            'created_by' => 1,
            'updated_by' => 1,
        ];

        $dpia = $this->repository->createDataProtectionImpactAssessment($data);

        $this->assertNotNull($dpia->id);
        $this->assertEquals('DPIA-2025-minimal', $dpia->dpia_code);
    }

    /**
     * Test updating a DPIA
     */
    public function test_update_dpia(): void
    {
        $dpia = DataProtectionImpactAssessment::factory()->create([
            'status' => Status::DRAFT->value,
            'risk_level' => RiskLevel::LOW->value,
        ]);

        $data = [
            'status' => Status::COMPLETED->value,
            'risk_level' => RiskLevel::MEDIUM->value,
        ];

        $updated = $this->repository->updateDataProtectionImpactAssessment($dpia, $data);

        $this->assertEquals(Status::COMPLETED->value, $updated->status);
        $this->assertEquals(RiskLevel::MEDIUM->value, $updated->risk_level);
        $this->assertDatabaseHas('data_protection_impact_assessments', [
            'id' => $dpia->id,
            'status' => Status::COMPLETED->value,
        ]);
    }

    /**
     * Test updating DPIA with partial data
     */
    public function test_update_dpia_with_partial_data(): void
    {
        $dpia = DataProtectionImpactAssessment::factory()->create([
            'status' => Status::DRAFT->value,
            'risk_level' => RiskLevel::LOW->value,
            'stage' => Stage::NECESSITY->value,
        ]);

        $data = ['status' => Status::COMPLETED->value];

        $updated = $this->repository->updateDataProtectionImpactAssessment($dpia, $data);

        $this->assertEquals(Status::COMPLETED->value, $updated->status);
        $this->assertEquals(RiskLevel::LOW->value, $updated->risk_level);
        $this->assertEquals(Stage::NECESSITY->value, $updated->stage);
    }

    /**
     * Test updating DPIA returns fresh instance
     */
    public function test_update_dpia_returns_fresh_instance(): void
    {
        $dpia = DataProtectionImpactAssessment::factory()->create(['status' => Status::DRAFT->value]);

        $data = ['status' => Status::COMPLETED->value];

        $updated = $this->repository->updateDataProtectionImpactAssessment($dpia, $data);

        $this->assertEquals(Status::COMPLETED->value, $updated->status);
    }

    /**
     * Test deleting a DPIA
     */
    public function test_delete_dpia(): void
    {
        $dpia = DataProtectionImpactAssessment::factory()->create();
        $id = $dpia->id;

        $result = $this->repository->deleteDataProtectionImpactAssessment($dpia);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('data_protection_impact_assessments', ['id' => $id]);
    }

    /**
     * Test empty filters return all records
     */
    public function test_get_filtered_dpias_with_empty_filters(): void
    {
        DataProtectionImpactAssessment::factory(5)->create();

        $result = $this->repository->getFilteredDataProtectionImpactAssessments([]);

        $this->assertCount(5, $result->items());
    }

    /**
     * Test filtering with non-matching criteria returns empty
     */
    public function test_get_filtered_dpias_with_non_matching_criteria(): void
    {
        DataProtectionImpactAssessment::factory(5)->create(['status' => Status::DRAFT->value]);

        $result = $this->repository->getFilteredDataProtectionImpactAssessments(['status' => Status::COMPLETED->value]);

        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }

    /**
     * Test pagination with total count
     */
    public function test_get_filtered_dpias_pagination_with_total(): void
    {
        DataProtectionImpactAssessment::factory(25)->create();

        $result = $this->repository->getFilteredDataProtectionImpactAssessments(['per_page' => 10]);

        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
        $this->assertTrue($result->hasPages());
    }

    /**
     * Test creating DPIA with related ROPA
     */
    public function test_create_dpia_with_related_ropa(): void
    {
        $ropa = RecordOfProcessingActivity::factory()->create();
        $data = DataProtectionImpactAssessment::factory()->make([
            'ropa_id' => $ropa->id,
        ])->toArray();

        $dpia = $this->repository->createDataProtectionImpactAssessment($data);

        $this->assertEquals($ropa->id, $dpia->ropa_id);
    }

    /**
     * Test filtering by partial name match
     */
    public function test_get_filtered_dpias_by_partial_name_match(): void
    {
        DataProtectionImpactAssessment::factory()->create(['dpia_name' => 'Customer Data Processing System']);
        DataProtectionImpactAssessment::factory()->create(['dpia_name' => 'Employee Records Management']);

        $result = $this->repository->getFilteredDataProtectionImpactAssessments(['name' => 'Data']);

        $this->assertCount(1, $result->items());
        $this->assertStringContainsString('Data', $result->items()[0]->dpia_name);
    }
}

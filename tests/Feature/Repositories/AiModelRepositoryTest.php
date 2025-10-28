<?php

namespace Tests\Feature\Repositories;

use App\Enums\AiModelDataset\EligibilityStatus;
use App\Enums\AiModelDataset\Role;
use App\Models\AiModel;
use App\Models\AiModelDataset;
use App\Models\AiModelVersion;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use App\Models\User;
use App\Models\Organization;
use App\Repositories\AiModelRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Enums\DevelopmentSource;
use App\Enums\BusinessStatus;
use App\Enums\OperationalStatus;
use App\Enums\StrategicImportance;
use App\Enums\OrganizationalRole;
use App\Enums\OwnershipType;
use App\Enums\PrimaryCategory;

use Illuminate\Foundation\Testing\WithFaker;

class AiModelRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected AiModelRepository $aiModelRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiModelRepository = app(AiModelRepository::class);
    }

    private function enumFirstValue(string $enumClass): string
    {
        return $enumClass::cases()[0]->value;
    }


    protected function validPayload(array $overrides = []): array
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create();

        return array_merge([
            'name' => 'Fraud Detector',
            'description' => 'Detects fraud in transactions.',
            'organization_id' => $org->id,
            'primary_category' => $this->enumFirstValue(PrimaryCategory::class),
            'type' => 'classification',
            'domain_specialization' => 'fraud_detection',
            'operational_status' => $this->enumFirstValue(OperationalStatus::class),
            'business_status' => $this->enumFirstValue(BusinessStatus::class),
            'total_versions' => 1,
            'strategic_importance' => $this->enumFirstValue(StrategicImportance::class),
            'regulatory_risk_classification' => 'low',
            'organizational_role' => $this->enumFirstValue(OrganizationalRole::class),
            'ownership_type' => $this->enumFirstValue(OwnershipType::class),
            'source_organization' => 'Data Science',
            'current_owner' => 'owner.user',
            'development_source' => $this->enumFirstValue(DevelopmentSource::class),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ], $overrides);
    }

    public function test_it_can_creates_an_ai_model(): void
    {
        $payload = $this->validPayload();

        $results = $this->aiModelRepository->create($payload); // assumes create(array $data): AiModel

        $this->assertInstanceOf(AiModel::class, $results);
        $this->assertDatabaseHas('ai_models', [
            'id' => $results->id,
            'name' => 'Fraud Detector',
        ]);
    }

    public function test_it_can_get_ai_models_by_organization_id(): void
    {
        $organization = Organization::factory()->create();

        AiModel::factory()->count(4)->create([
            'organization_id' => $organization->id,
        ]);

        $results = $this->aiModelRepository->getAllAiModelsByOrganizationID($organization->id);

        $this->assertCount(4, $results);
        $this->assertEquals($organization->id, $results->first()->organization_id);
    }

    public function test_it_can_get_the_ai_model_by_id(): void
    {
        $aiModel = AiModel::factory()->create();
        $results = $this->aiModelRepository->getAiModelByID($aiModel->id);
        $this->assertInstanceOf(AiModel::class, $results);
        $this->assertEquals($aiModel->id, $results->id);
        $this->assertEquals($aiModel->name, $results->name);
        $this->assertDatabaseHas('ai_models', [
            'id' => $aiModel->id,
            'name' => $aiModel->name,
        ]);
    }

    /**
     * Test assign dataset with pretrain role (no snapshot required).
     */
    public function test_assign_dataset_with_pretrain_role_without_snapshot(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();

        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'role' => Role::PRETRAIN->value,
        ];

        $result = $this->aiModelRepository->assignDataset($data);

        $this->assertInstanceOf(AiModelDataset::class, $result);
        $this->assertEquals($aiModel->id, $result->ai_model_id);
        $this->assertEquals(Role::PRETRAIN->value, $result->role);
        $this->assertNull($result->dataset_snapshot_id);
        $this->assertDatabaseHas('ai_model_dataset', [
            'ai_model_id' => $aiModel->id,
            'role' => Role::PRETRAIN->value,
        ]);
    }

    /**
     * Test assign dataset with train role and snapshot.
     */
    public function test_assign_dataset_with_train_role_and_snapshot(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();
        $snapshot = DatasetSnapshot::factory()->create(['dataset_id' => $dataset->id]);

        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'dataset_snapshot_id' => $snapshot->id,
            'role' => Role::TRAIN->value,
        ];

        $result = $this->aiModelRepository->assignDataset($data);

        $this->assertInstanceOf(AiModelDataset::class, $result);
        $this->assertEquals($snapshot->id, $result->dataset_snapshot_id);
        $this->assertEquals(Role::TRAIN->value, $result->role);
        $this->assertDatabaseHas('ai_model_dataset', [
            'ai_model_id' => $aiModel->id,
            'dataset_snapshot_id' => $snapshot->id,
            'role' => Role::TRAIN->value,
        ]);
    }

    /**
     * Test assign dataset with validation role and snapshot.
     */
    public function test_assign_dataset_with_validation_role_and_snapshot(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $snapshot = DatasetSnapshot::factory()->create();

        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_snapshot_id' => $snapshot->id,
            'role' => Role::VALIDATION->value,
        ];

        $result = $this->aiModelRepository->assignDataset($data);

        $this->assertInstanceOf(AiModelDataset::class, $result);
        $this->assertEquals(Role::VALIDATION->value, $result->role);
        $this->assertEquals($snapshot->id, $result->dataset_snapshot_id);
    }

    /**
     * Test assign dataset with test role and snapshot.
     */
    public function test_assign_dataset_with_test_role_and_snapshot(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $snapshot = DatasetSnapshot::factory()->create();

        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_snapshot_id' => $snapshot->id,
            'role' => Role::TEST->value,
        ];

        $result = $this->aiModelRepository->assignDataset($data);

        $this->assertInstanceOf(AiModelDataset::class, $result);
        $this->assertEquals(Role::TEST->value, $result->role);
    }

    /**
     * Test assign dataset with eval_benchmark role and snapshot.
     */
    public function test_assign_dataset_with_eval_benchmark_role_and_snapshot(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $snapshot = DatasetSnapshot::factory()->create();

        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_snapshot_id' => $snapshot->id,
            'role' => Role::EVAL_BENCHMARK->value,
        ];

        $result = $this->aiModelRepository->assignDataset($data);

        $this->assertInstanceOf(AiModelDataset::class, $result);
        $this->assertEquals(Role::EVAL_BENCHMARK->value, $result->role);
    }

    /**
     * Test assign dataset with all optional fields.
     */
    public function test_assign_dataset_with_all_optional_fields(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();
        $snapshot = DatasetSnapshot::factory()->create(['dataset_id' => $dataset->id]);

        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'dataset_snapshot_id' => $snapshot->id,
            'role' => Role::TRAIN->value,
            'access_path' => '/data/training/path',
            'transform_pack_link' => 'https://transforms.example.com/pack123',
            'license_check_ref' => 'LIC-123456',
            'privacy_check_ref' => 'PRI-789012',
            'eligibility_status' => EligibilityStatus::ELIGIBLE->value,
            'notes' => 'This is a test note for the dataset assignment.',
        ];

        $result = $this->aiModelRepository->assignDataset($data);

        $this->assertInstanceOf(AiModelDataset::class, $result);
        $this->assertEquals('/data/training/path', $result->access_path);
        $this->assertEquals('https://transforms.example.com/pack123', $result->transform_pack_link);
        $this->assertEquals('LIC-123456', $result->license_check_ref);
        $this->assertEquals('PRI-789012', $result->privacy_check_ref);
        $this->assertEquals(EligibilityStatus::ELIGIBLE->value, $result->eligibility_status);
        $this->assertEquals('This is a test note for the dataset assignment.', $result->notes);
    }

    /**
     * Test assign dataset with fine_tune role (no snapshot required).
     */
    public function test_assign_dataset_with_fine_tune_role_without_snapshot(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();

        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'role' => Role::FINE_TUNE->value,
        ];

        $result = $this->aiModelRepository->assignDataset($data);

        $this->assertInstanceOf(AiModelDataset::class, $result);
        $this->assertEquals(Role::FINE_TUNE->value, $result->role);
        $this->assertNull($result->dataset_snapshot_id);
    }

    /**
     * Test assign dataset with rag_corpus role (no snapshot required).
     */
    public function test_assign_dataset_with_rag_corpus_role_without_snapshot(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();

        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'role' => Role::RAG_CORPUS->value,
        ];

        $result = $this->aiModelRepository->assignDataset($data);

        $this->assertInstanceOf(AiModelDataset::class, $result);
        $this->assertEquals(Role::RAG_CORPUS->value, $result->role);
    }

    /**
     * Test assign dataset with eligibility status eligible_with_conditions.
     */
    public function test_assign_dataset_with_eligibility_status_eligible_with_conditions(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();

        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'role' => Role::PRETRAIN->value,
            'eligibility_status' => EligibilityStatus::ELIGIBLE_WITH_CONDITIONS->value,
            'notes' => 'Requires additional privacy review.',
        ];

        $result = $this->aiModelRepository->assignDataset($data);

        $this->assertEquals(EligibilityStatus::ELIGIBLE_WITH_CONDITIONS->value, $result->eligibility_status);
        $this->assertEquals('Requires additional privacy review.', $result->notes);
    }

    /**
     * Test assign dataset with eligibility status not_eligible.
     */
    public function test_assign_dataset_with_eligibility_status_not_eligible(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $dataset = Dataset::factory()->create();

        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'dataset_id' => $dataset->id,
            'role' => Role::PRETRAIN->value,
            'eligibility_status' => EligibilityStatus::NOT_ELIGIBLE->value,
            'notes' => 'License restrictions prevent usage.',
        ];

        $result = $this->aiModelRepository->assignDataset($data);

        $this->assertEquals(EligibilityStatus::NOT_ELIGIBLE->value, $result->eligibility_status);
        $this->assertEquals('License restrictions prevent usage.', $result->notes);
    }
}

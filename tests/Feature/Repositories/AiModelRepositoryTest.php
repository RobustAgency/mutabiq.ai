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
use App\Models\Stakeholder;
use App\Models\Vendor;

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
        $sourceOrg = Stakeholder::factory()->create(['type' => 'vendor_org']);
        $vendor = Vendor::factory()->create();

        return array_merge([
            'name' => 'Fraud Detector',
            'description' => 'Detects fraud in transactions.',
            'organization_id' => $org->id,
            'source_org_stakeholder_id' => $sourceOrg->id,
            'vendor_id' => $vendor->id,
            'primary_category' => $this->enumFirstValue(PrimaryCategory::class),
            'type' => 'classification',
            'domain_specialization' => 'fraud_detection',
            'operational_status' => $this->enumFirstValue(OperationalStatus::class),
            'business_status' => $this->enumFirstValue(BusinessStatus::class),
            'regulatory_risk_classification' => 'low',
            'ownership_type' => $this->enumFirstValue(OwnershipType::class),
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

        AiModel::factory()->count(3)->create([
            'organization_id' => $organization->id,
        ]);
        $results = $this->aiModelRepository->getAllAiModelsByOrganizationID($organization->id);

        $this->assertCount(3, $results);
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
}

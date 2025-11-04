<?php

namespace Tests\Feature\Controllers\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Enums\PrimaryCategory;
use App\Enums\OperationalStatus;
use App\Enums\BusinessStatus;
use App\Enums\StrategicImportance;
use App\Enums\OrganizationalRole;
use App\Enums\OwnershipType;
use App\Enums\DevelopmentSource;
use App\Models\Organization;
use App\Models\User;
use App\Models\AiModel;
use App\Models\Stakeholder;
use App\Models\Vendor;
use Tests\TestCase;

class AiModelControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected string $baseEndpoint = '/api/ai-models';

    protected function enumValue(string $enumClass): string
    {
        return $enumClass::cases()[0]->value;
    }

    protected function validPayload(array $overrides = []): array
    {
        $sourceOrg = Stakeholder::factory()->create(['type' => 'vendor_org']);
        $custodian = Stakeholder::factory()->create(['type' => 'person']);
        $vendor = Vendor::factory()->create();

        return array_merge([
            'name' => 'Fraud Detector',
            'description' => 'Detects fraudulent patterns',
            'source_org_stakeholder_id' => $sourceOrg->id,
            'owner_stakeholder_id' => $custodian->id,
            'vendor_id' => $vendor->id,
            'organizational_role' => OrganizationalRole::cases()[0]->value,
            'primary_category' => PrimaryCategory::cases()[0]->value,
            'type' => 'classification',
            'domain_specialization' => 'fraud_detection',
            'operational_status' => OperationalStatus::cases()[0]->value,
            'business_status' => BusinessStatus::cases()[0]->value,
            'regulatory_risk_classification' => 'low',
            'ownership_type' => OwnershipType::cases()[0]->value,
            'development_source' => DevelopmentSource::cases()[0]->value,
            'current_owner' => 'ml.owner',
            'creator_email' => 'ml.creator@example.com',
        ], $overrides);
    }

    public function test_index_fails_when_user_without_organization(): void
    {
        $user = User::factory()->create(['organization_id' => null]);

        $response = $this->actingAs($user)->getJson($this->baseEndpoint);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'true',
                'message' => 'User does not belong to any organization',
            ]);
    }

    public function test_user_can_get_ai_models_for_his_organization(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        // Models in user's org
        AiModel::factory()->count(3)->create(['organization_id' => $org->id]);
        // Model in another org (should not appear)
        AiModel::factory()->create();

        $response = $this->actingAs($user)->getJson($this->baseEndpoint);

        $response->assertOk()
            ->assertJson([
                'error' => 'false',
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
        $this->assertTrue(collect($data)->every(fn($m) => $m['id'] !== null));
    }

    public function test_store_fails_without_organization(): void
    {
        $user = User::factory()->create(['organization_id' => null]);

        $response = $this->actingAs($user)->postJson($this->baseEndpoint, $this->validPayload());

        $response->assertStatus(403)
            ->assertJson(['error' => 'true']);
    }

    public function test_user_can_create_ai_model(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $payload = $this->validPayload();

        $response = $this->actingAs($user)->postJson($this->baseEndpoint, $payload);

        $response->assertCreated()
            ->assertJson([
                'error' => 'false',
                'message' => 'AI Model created successfully',
            ]);

        $this->assertDatabaseHas('ai_models', [
            'name' => $payload['name'],
            'organization_id' => $org->id,
        ]);
    }

    public function test_it_can_store_validation_errors(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        // Remove required fields
        $payload = $this->validPayload([
            'name' => '',
            'primary_category' => 'invalid_enum_value',
        ]);

        $response = $this->actingAs($user)->postJson($this->baseEndpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'primary_category']);
    }

    public function test_user_can_get_ai_model_resource(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $model = AiModel::factory()->create([
            'organization_id' => $org->id,
            'created_by' => $user->email,
            'updated_by' => $user->email,
        ]);

        $response = $this->actingAs($user)->getJson($this->baseEndpoint . '/' . $model->id);

        $response->assertOk()
            ->assertJson([
                'error' => 'false',
                'data' => [
                    'id' => $model->id,
                    'name' => $model->name,
                ],
            ]);
    }
}

<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiModel;
use App\Enums\OwnershipType;
use App\Models\Organization;
use App\Enums\BusinessStatus;
use App\Enums\PrimaryCategory;
use App\Enums\OrganizationalRole;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        $org = Organization::factory()->create();
        $businessOwner = User::factory()->create();
        $custodian = User::factory()->create();
        $createdBy = User::factory()->create();
        $updatedBy = User::factory()->create();

        return array_merge([
            'name' => 'Fraud Detector',
            'organization_id' => $org->id,
            'category' => PrimaryCategory::cases()[0]->value,
            'type' => 'classification',
            'technical_domain' => 'fraud_detection',
            'purpose' => 'Detects fraudulent patterns',
            'criticality_level' => 'high',
            'business_adoption_status' => BusinessStatus::cases()[0]->value,
            'regulatory_risk_tier' => 'low',
            'eu_ai_category' => 'minimal',
            'ownership_category' => OwnershipType::cases()[0]->value,
            'responsible_org_role' => OrganizationalRole::cases()[0]->value,
            'business_owner_id' => $businessOwner->id,
            'custodian_id' => $custodian->id,
            'created_by' => $createdBy->id,
            'updated_by' => $updatedBy->id,
        ], $overrides);
    }

    public function test_index_fails_when_user_without_organization(): void
    {
        $user = User::factory()->create(['organization_id' => null]);

        $response = $this->actingAs($user)->getJson($this->baseEndpoint);
        $response->assertOk()
            ->assertJson([
                'error' => 'false',
            ]);

        $this->assertEquals(0, $response->json('data.total'));
    }

    public function test_user_can_get_ai_models_for_his_organization(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        AiModel::factory()->count(3)->create(['organization_id' => $org->id]);

        AiModel::factory()->create();

        $response = $this->actingAs($user)->getJson($this->baseEndpoint);

        $response->assertOk()
            ->assertJson([
                'error' => 'false',
            ]);

        $this->assertEquals(3, $response->json('data.total'));
        $data = $response->json('data.data');
        $this->assertCount(3, $data);
        $this->assertTrue(collect($data)->every(fn ($m) => $m['id'] !== null));
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

        $payload = $this->validPayload(['organization_id' => $org->id]);

        $response = $this->actingAs($user)->postJson($this->baseEndpoint, $payload);

        $response->assertCreated()
            ->assertJson([
                'error' => 'false',
                'message' => 'AI Model created successfully',
            ]);

        $this->assertDatabaseHas('ai_models', [
            'name' => $payload['name'],
            'organization_id' => $org->id,
            'category' => $payload['category'],
            'type' => $payload['type'],
        ]);
    }

    public function test_it_can_store_validation_errors(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        // Remove required fields
        $payload = $this->validPayload([
            'name' => '',
            'category' => 'invalid_enum_value',
        ]);

        $response = $this->actingAs($user)->postJson($this->baseEndpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'category']);
    }

    public function test_user_can_get_ai_model_resource(): void
    {
        $org = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $org->id]);

        $model = AiModel::factory()->create([
            'organization_id' => $org->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->getJson($this->baseEndpoint.'/'.$model->id);

        $response->assertOk()
            ->assertJson([
                'error' => 'false',
                'data' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'category' => $model->category,
                    'type' => $model->type,
                ],
            ]);
    }
}

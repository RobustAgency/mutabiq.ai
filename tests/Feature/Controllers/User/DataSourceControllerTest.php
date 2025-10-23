<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\DataSource\AccessMethod;
use App\Enums\DataSource\CloudProvider;
use App\Enums\DataSource\DataClassification;
use App\Enums\DataSource\DataResidency;
use App\Enums\DataSource\HostingModel;
use App\Enums\DataSource\ServiceModel;
use App\Enums\DataSource\SystemType;
use App\Models\DataSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataSourceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function enumFirstValue(string $enumClass): string
    {
        return $enumClass::cases()[0]->value;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Customer Database',
            'system_type' => $this->enumFirstValue(SystemType::class),
            'owner_team' => 'Engineering',
            'data_domains' => ['Customer', 'Finance'],
            'access_method' => $this->enumFirstValue(AccessMethod::class),
            'residency' => $this->enumFirstValue(DataResidency::class),
            'classification' => $this->enumFirstValue(DataClassification::class),
            'hosting_model' => $this->enumFirstValue(HostingModel::class),
            'service_model' => $this->enumFirstValue(ServiceModel::class),
            'cloud_provider' => $this->enumFirstValue(CloudProvider::class),
            'primary_region' => 'us-east-1',
            'secondary_region' => 'us-west-2',
            'network_ref' => 'vpc-12345678',
            'retention_policy_ref' => 'policy-90d',
            'catalog_uri' => 'https://catalog.example.com',
        ], $overrides);
    }

    public function test_user_can_get_paginated_data_sources(): void
    {
        DataSource::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/data-sources');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data',
                    'total',
                    'per_page',
                ],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Data sources retrieved successfully',
            ]);

        $this->assertEquals(20, $response->json('data.total'));
    }

    public function test_user_can_set_custom_per_page(): void
    {
        DataSource::factory()->count(30)->create();

        $response = $this->actingAs($this->user)->getJson('/api/data-sources?per_page=5');

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.per_page'));
        $this->assertEquals(30, $response->json('data.total'));
    }

    public function test_user_can_create_data_source(): void
    {
        $data = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/data-sources', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Data source created successfully',
                'data' => [
                    'name' => 'Customer Database',
                    'owner_team' => 'Engineering',
                ],
            ]);

        $this->assertDatabaseHas('data_sources', [
            'name' => 'Customer Database',
            'owner_team' => 'Engineering',
        ]);
    }

    public function test_user_cannot_create_data_source_without_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/data-sources', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'system_type',
                'owner_team',
                'data_domains',
                'access_method',
                'residency',
                'classification',
                'hosting_model',
                'service_model',
                'cloud_provider',
            ]);
    }

    public function test_user_cannot_create_data_source_with_invalid_enum_values(): void
    {
        $data = $this->validPayload([
            'system_type' => 'invalid_type',
            'access_method' => 'invalid_method',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/data-sources', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['system_type', 'access_method']);
    }

    public function test_user_can_view_single_data_source(): void
    {
        $dataSource = DataSource::factory()->create([
            'name' => 'Test Database',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/data-sources/' . $dataSource->id);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Data source retrieved successfully',
                'data' => [
                    'id' => $dataSource->id,
                    'name' => 'Test Database',
                ],
            ]);
    }

    public function test_user_can_update_data_source(): void
    {
        $dataSource = DataSource::factory()->create([
            'name' => 'Old Name',
            'owner_team' => 'Old Team',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'owner_team' => 'Updated Team',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-sources/' . $dataSource->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Data source updated successfully',
                'data' => [
                    'name' => 'Updated Name',
                    'owner_team' => 'Updated Team',
                ],
            ]);

        $this->assertDatabaseHas('data_sources', [
            'id' => $dataSource->id,
            'name' => 'Updated Name',
            'owner_team' => 'Updated Team',
        ]);
    }

    public function test_user_cannot_update_data_source_with_invalid_enum_values(): void
    {
        $dataSource = DataSource::factory()->create();

        $updateData = [
            'system_type' => 'invalid_type',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-sources/' . $dataSource->id, $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['system_type']);
    }

    public function test_user_can_delete_data_source(): void
    {
        $dataSource = DataSource::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson('/api/data-sources/' . $dataSource->id);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Data source deleted successfully',
            ]);

        $this->assertDatabaseMissing('data_sources', [
            'id' => $dataSource->id,
        ]);
    }

    public function test_data_domains_are_stored_as_array(): void
    {
        $data = $this->validPayload([
            'data_domains' => ['Customer', 'Finance', 'Marketing'],
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/data-sources', $data);

        $response->assertStatus(201);

        $dataSource = DataSource::find($response->json('data.id'));
        $this->assertIsArray($dataSource->data_domains);
        $this->assertCount(3, $dataSource->data_domains);
    }

    public function test_nullable_fields_can_be_null(): void
    {
        $data = $this->validPayload([
            'primary_region' => null,
            'secondary_region' => null,
            'network_ref' => null,
            'retention_policy_ref' => null,
            'catalog_uri' => null,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/data-sources', $data);

        $response->assertStatus(201);

        $dataSource = DataSource::find($response->json('data.id'));
        $this->assertNull($dataSource->primary_region);
        $this->assertNull($dataSource->secondary_region);
        $this->assertNull($dataSource->network_ref);
        $this->assertNull($dataSource->retention_policy_ref);
        $this->assertNull($dataSource->catalog_uri);
    }

    public function test_catalog_uri_must_be_valid_url(): void
    {
        $data = $this->validPayload([
            'catalog_uri' => 'not-a-valid-url',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/data-sources', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['catalog_uri']);
    }
}

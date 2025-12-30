<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\DataSource;
use App\Models\Organization;
use App\Enums\DataSource\Status;
use App\Enums\DataSource\OwnerTeam;
use App\Enums\DataSource\DataDomain;
use App\Enums\DataSource\SystemType;
use App\Enums\DataSource\HostingModel;
use App\Enums\DataSource\DataResidency;
use App\Enums\DataSource\CriticalityLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataSourceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    private function enumFirstValue(string $enumClass): string
    {
        return $enumClass::cases()[0]->value;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Customer Database',
            'description' => 'Central customer database',
            'system_type' => $this->enumFirstValue(SystemType::class),
            'owner_team' => $this->enumFirstValue(OwnerTeam::class),
            'data_domains' => [
                $this->enumFirstValue(DataDomain::class),
            ],
            'residency' => $this->enumFirstValue(DataResidency::class),
            'criticality_level' => $this->enumFirstValue(CriticalityLevel::class),
            'hosting_model' => $this->enumFirstValue(HostingModel::class),
            'technical_owner' => $this->enumFirstValue(OwnerTeam::class),
            'business_owner' => $this->enumFirstValue(OwnerTeam::class),
            'last_review_date' => now()->format('Y-m-d'),
            'next_review_date' => now()->addMonths(3)->format('Y-m-d'),
            'status' => $this->enumFirstValue(Status::class),
        ], $overrides);
    }

    public function test_user_can_get_paginated_data_sources(): void
    {
        DataSource::factory()->count(20)->create(['organization_id' => $this->organization->id]);

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
        DataSource::factory()->count(30)->create(['organization_id' => $this->organization->id]);

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
                    'description' => 'Central customer database',
                ],
            ]);

        $this->assertDatabaseHas('data_sources', [
            'name' => 'Customer Database',
            'description' => 'Central customer database',
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
                'residency',
                'hosting_model',
                'technical_owner',
                'business_owner',
                'status',
            ]);
    }

    public function test_user_cannot_create_data_source_with_invalid_enum_values(): void
    {
        $data = $this->validPayload([
            'system_type' => 'invalid_type',
            'status' => 'invalid_status',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/data-sources', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['system_type', 'status']);
    }

    public function test_user_can_view_single_data_source(): void
    {
        $dataSource = DataSource::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Database',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/data-sources/'.$dataSource->id);

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
            'organization_id' => $this->organization->id,
            'name' => 'Old Name',
            'description' => 'Old Description',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-sources/'.$dataSource->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Data source updated successfully',
                'data' => [
                    'name' => 'Updated Name',
                    'description' => 'Updated Description',
                ],
            ]);

        $this->assertDatabaseHas('data_sources', [
            'id' => $dataSource->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ]);
    }

    public function test_user_cannot_update_data_source_with_invalid_enum_values(): void
    {
        $dataSource = DataSource::factory()->create(['organization_id' => $this->organization->id]);

        $updateData = [
            'system_type' => 'invalid_type',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/data-sources/'.$dataSource->id, $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['system_type']);
    }

    public function test_user_can_delete_data_source(): void
    {
        $dataSource = DataSource::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->deleteJson('/api/data-sources/'.$dataSource->id);

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
            'data_domains' => [
                DataDomain::cases()[0]->value,
                DataDomain::cases()[1]->value,
            ],
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/data-sources', $data);

        $response->assertStatus(201);

        $dataSource = DataSource::find($response->json('data.id'));
        $this->assertIsArray($dataSource->data_domains);
        $this->assertCount(2, $dataSource->data_domains);
    }

    public function test_nullable_fields_can_be_null(): void
    {
        $data = $this->validPayload([
            'description' => null,
            'criticality_level' => null,
            'last_review_date' => null,
            'next_review_date' => null,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/data-sources', $data);

        $response->assertStatus(201);

        $dataSource = DataSource::find($response->json('data.id'));
        $this->assertNull($dataSource->description);
        $this->assertNull($dataSource->criticality_level);
        $this->assertNull($dataSource->last_review_date);
        $this->assertNull($dataSource->next_review_date);
    }

    public function test_next_review_date_must_be_after_or_equal_last_review_date(): void
    {
        $data = $this->validPayload([
            'last_review_date' => now()->format('Y-m-d'),
            'next_review_date' => now()->subDays(1)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/data-sources', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['next_review_date']);
    }
}

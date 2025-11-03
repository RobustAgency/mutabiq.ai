<?php

namespace Tests\Feature\Repositories;

use App\Enums\DataSource\AccessMethod;
use App\Enums\DataSource\CloudProvider;
use App\Enums\DataSource\DataClassification;
use App\Enums\DataSource\DataResidency;
use App\Enums\DataSource\HostingModel;
use App\Enums\DataSource\ServiceModel;
use App\Enums\DataSource\SystemType;
use App\Models\DataSource;
use App\Models\Organization;
use App\Repositories\DataSourceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DataSourceRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected DataSourceRepository $dataSourceRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dataSourceRepository = app(DataSourceRepository::class);
    }

    private function enumFirstValue(string $enumClass): string
    {
        return $enumClass::cases()[0]->value;
    }

    protected function validPayload(array $overrides = []): array
    {
        $organization = Organization::factory()->create();
        return array_merge([
            'organization_id' => $organization->id,
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

    public function test_it_can_get_paginated_data_sources(): void
    {
        $organization = Organization::factory()->create();
        DataSource::factory()->count(25)->create(['organization_id' => $organization->id]);

        $results = $this->dataSourceRepository->getPaginatedDataSources($organization->id, 10);

        $this->assertCount(10, $results->items());
        $this->assertEquals(25, $results->total());
    }

    public function test_it_can_get_data_source_by_id(): void
    {
        $dataSource = DataSource::factory()->create();

        $result = $this->dataSourceRepository->getDataSourceById($dataSource->id);

        $this->assertInstanceOf(DataSource::class, $result);
        $this->assertEquals($dataSource->id, $result->id);
        $this->assertEquals($dataSource->name, $result->name);
    }

    public function test_it_returns_null_when_data_source_not_found(): void
    {
        $result = $this->dataSourceRepository->getDataSourceById(9999);

        $this->assertNull($result);
    }

    public function test_it_can_create_a_data_source(): void
    {
        $payload = $this->validPayload();

        $result = $this->dataSourceRepository->createDataSource($payload);

        $this->assertInstanceOf(DataSource::class, $result);
        $this->assertDatabaseHas('data_sources', [
            'id' => $result->id,
            'name' => 'Customer Database',
            'owner_team' => 'Engineering',
        ]);
    }

    public function test_it_can_update_a_data_source(): void
    {
        $dataSource = DataSource::factory()->create([
            'name' => 'Old Name',
            'owner_team' => 'Old Team',
        ]);

        $updateData = [
            'name' => 'New Name',
            'owner_team' => 'New Team',
        ];

        $result = $this->dataSourceRepository->updateDataSource($dataSource, $updateData);

        $this->assertTrue($result);
        $this->assertDatabaseHas('data_sources', [
            'id' => $dataSource->id,
            'name' => 'New Name',
            'owner_team' => 'New Team',
        ]);
    }

    public function test_it_can_delete_a_data_source(): void
    {
        $dataSource = DataSource::factory()->create();

        $result = $this->dataSourceRepository->delete($dataSource);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('data_sources', [
            'id' => $dataSource->id,
        ]);
    }

    public function test_it_stores_data_domains_as_array(): void
    {
        $payload = $this->validPayload([
            'data_domains' => ['Customer', 'Finance', 'Marketing'],
        ]);

        $result = $this->dataSourceRepository->createDataSource($payload);

        $this->assertIsArray($result->data_domains);
        $this->assertCount(3, $result->data_domains);
        $this->assertContains('Customer', $result->data_domains);
        $this->assertContains('Finance', $result->data_domains);
        $this->assertContains('Marketing', $result->data_domains);
    }

    public function test_it_handles_nullable_fields(): void
    {
        $payload = $this->validPayload([
            'primary_region' => null,
            'secondary_region' => null,
            'network_ref' => null,
            'retention_policy_ref' => null,
            'catalog_uri' => null,
        ]);

        $result = $this->dataSourceRepository->createDataSource($payload);

        $this->assertInstanceOf(DataSource::class, $result);
        $this->assertNull($result->primary_region);
        $this->assertNull($result->secondary_region);
        $this->assertNull($result->network_ref);
        $this->assertNull($result->retention_policy_ref);
        $this->assertNull($result->catalog_uri);
    }
}

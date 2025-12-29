<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\DataSource;
use App\Models\Organization;
use App\Enums\DataSource\Status;
use App\Enums\DataSource\OwnerTeam;
use App\Enums\DataSource\DataDomain;
use App\Enums\DataSource\SystemType;
use App\Enums\DataSource\HostingModel;
use App\Enums\DataSource\DataResidency;
use App\Enums\DataSource\CriticalityLevel;
use App\Repositories\DataSourceRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    public function test_it_can_get_paginated_data_sources(): void
    {
        $organization = Organization::factory()->create();
        DataSource::factory()->count(25)->create(['organization_id' => $organization->id]);

        $results = $this->dataSourceRepository->getFilteredDataSources(['organization_id' => $organization->id, 'per_page' => 10]);

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
            'description' => 'Central customer database',
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
            'description' => null,
            'criticality_level' => null,
            'last_review_date' => null,
            'next_review_date' => null,
        ]);

        $result = $this->dataSourceRepository->createDataSource($payload);

        $this->assertInstanceOf(DataSource::class, $result);
        $this->assertNull($result->description);
        $this->assertNull($result->criticality_level);
        $this->assertNull($result->last_review_date);
        $this->assertNull($result->next_review_date);
    }

    public function test_filter_by_name(): void
    {
        $organization = Organization::factory()->create();

        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Customer Database',
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Employee Records',
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Customer Analytics',
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'name' => 'Customer',
        ];
        $result = $this->dataSourceRepository->getFilteredDataSources($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $dataSource) {
            $this->assertStringContainsString('Customer', $dataSource->name);
        }
    }

    public function test_filter_by_name_is_case_insensitive(): void
    {
        $organization = Organization::factory()->create();

        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'PRODUCTION Database',
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'development system',
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Production API',
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'name' => 'production',
        ];
        $result = $this->dataSourceRepository->getFilteredDataSources($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_filter_by_system_type(): void
    {
        $organization = Organization::factory()->create();

        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'system_type' => SystemType::APPLICATION_DB->value,
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'system_type' => SystemType::OPERATIONAL_API->value,
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'system_type' => SystemType::APPLICATION_DB->value,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'system_type' => SystemType::APPLICATION_DB->value,
        ];
        $result = $this->dataSourceRepository->getFilteredDataSources($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $dataSource) {
            $this->assertEquals(SystemType::APPLICATION_DB->value, $dataSource->system_type);
        }
    }

    public function test_filter_by_criticality_level(): void
    {
        $organization = Organization::factory()->create();

        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'criticality_level' => CriticalityLevel::CRITICAL->value,
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'criticality_level' => CriticalityLevel::HIGH->value,
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'criticality_level' => CriticalityLevel::CRITICAL->value,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'criticality_level' => CriticalityLevel::CRITICAL->value,
        ];
        $result = $this->dataSourceRepository->getFilteredDataSources($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $dataSource) {
            $this->assertEquals(CriticalityLevel::CRITICAL->value, $dataSource->criticality_level);
        }
    }

    public function test_filter_by_status(): void
    {
        $organization = Organization::factory()->create();

        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'status' => Status::ACTIVE->value,
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'status' => Status::DRAFT->value,
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'status' => Status::ACTIVE->value,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'status' => Status::ACTIVE->value,
        ];
        $result = $this->dataSourceRepository->getFilteredDataSources($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $dataSource) {
            $this->assertEquals(Status::ACTIVE->value, $dataSource->status);
        }
    }

    public function test_filter_by_date_range(): void
    {
        $organization = Organization::factory()->create();

        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(10),
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(5),
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(1),
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'from' => now()->subDays(7)->format('Y-m-d'),
            'to' => now()->subDays(2)->format('Y-m-d'),
        ];
        $result = $this->dataSourceRepository->getFilteredDataSources($filters);

        $this->assertCount(1, $result->items());
    }

    public function test_filter_by_from_date_only(): void
    {
        $organization = Organization::factory()->create();

        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(10),
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(5),
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(1),
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'from' => now()->subDays(6)->format('Y-m-d'),
        ];
        $result = $this->dataSourceRepository->getFilteredDataSources($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_filter_by_to_date_only(): void
    {
        $organization = Organization::factory()->create();

        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(10),
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(5),
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(1),
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'to' => now()->subDays(6)->format('Y-m-d'),
        ];
        $result = $this->dataSourceRepository->getFilteredDataSources($filters);

        $this->assertCount(1, $result->items());
    }

    public function test_filter_by_multiple_filters(): void
    {
        $organization = Organization::factory()->create();

        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Customer Database',
            'system_type' => SystemType::APPLICATION_DB->value,
            'status' => Status::ACTIVE->value,
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Customer API',
            'system_type' => SystemType::OPERATIONAL_API->value,
            'status' => Status::ACTIVE->value,
        ]);
        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Customer Analytics',
            'system_type' => SystemType::APPLICATION_DB->value,
            'status' => Status::DRAFT->value,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'name' => 'Customer',
            'system_type' => SystemType::APPLICATION_DB->value,
            'status' => Status::ACTIVE->value,
        ];
        $result = $this->dataSourceRepository->getFilteredDataSources($filters);

        $this->assertCount(1, $result->items());
        $dataSource = $result->items()[0];
        $this->assertStringContainsString('Customer', $dataSource->name);
        $this->assertEquals(SystemType::APPLICATION_DB->value, $dataSource->system_type);
        $this->assertEquals(Status::ACTIVE->value, $dataSource->status);
    }

    public function test_filter_by_different_system_types(): void
    {
        $organization = Organization::factory()->create();

        DataSource::factory()->create(['organization_id' => $organization->id, 'system_type' => SystemType::APPLICATION_DB->value]);
        DataSource::factory()->create(['organization_id' => $organization->id, 'system_type' => SystemType::OPERATIONAL_API->value]);
        DataSource::factory()->create(['organization_id' => $organization->id, 'system_type' => SystemType::DATA_LAKE->value]);

        $applicationDbResult = $this->dataSourceRepository->getFilteredDataSources(['organization_id' => $organization->id, 'system_type' => SystemType::APPLICATION_DB->value]);
        $apiResult = $this->dataSourceRepository->getFilteredDataSources(['organization_id' => $organization->id, 'system_type' => SystemType::OPERATIONAL_API->value]);
        $dataLakeResult = $this->dataSourceRepository->getFilteredDataSources(['organization_id' => $organization->id, 'system_type' => SystemType::DATA_LAKE->value]);

        $this->assertCount(1, $applicationDbResult->items());
        $this->assertCount(1, $apiResult->items());
        $this->assertCount(1, $dataLakeResult->items());
    }

    public function test_filter_returns_empty_when_no_matches(): void
    {
        $organization = Organization::factory()->create();

        DataSource::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Test Source',
            'system_type' => SystemType::APPLICATION_DB->value,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'name' => 'NonExistent',
            'system_type' => SystemType::OPERATIONAL_API->value,
        ];
        $result = $this->dataSourceRepository->getFilteredDataSources($filters);

        $this->assertCount(0, $result->items());
    }

    public function test_filter_with_per_page_parameter(): void
    {
        $organization = Organization::factory()->create();

        DataSource::factory()->count(20)->create([
            'organization_id' => $organization->id,
            'system_type' => SystemType::APPLICATION_DB->value,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'system_type' => SystemType::APPLICATION_DB->value,
            'per_page' => 8,
        ];
        $result = $this->dataSourceRepository->getFilteredDataSources($filters);

        $this->assertCount(8, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(8, $result->perPage());
    }

    public function test_filter_by_organization_id(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        DataSource::factory()->count(5)->create(['organization_id' => $org1->id]);
        DataSource::factory()->count(3)->create(['organization_id' => $org2->id]);

        $result1 = $this->dataSourceRepository->getFilteredDataSources(['organization_id' => $org1->id]);
        $result2 = $this->dataSourceRepository->getFilteredDataSources(['organization_id' => $org2->id]);

        $this->assertEquals(5, $result1->total());
        $this->assertEquals(3, $result2->total());
    }

    public function test_filter_uses_default_per_page(): void
    {
        $organization = Organization::factory()->create();

        DataSource::factory()->count(20)->create(['organization_id' => $organization->id]);

        $result = $this->dataSourceRepository->getFilteredDataSources(['organization_id' => $organization->id]);

        $this->assertEquals(15, $result->perPage());
    }
}

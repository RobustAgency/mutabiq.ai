<?php

namespace Tests\Feature\Repositories;

use App\Enums\UseCase\BusinessDomain;
use App\Enums\UseCase\DataAvailabilityStatus;
use App\Enums\UseCase\DataReadiness;
use App\Enums\UseCase\DataSensitivity;
use App\Enums\UseCase\Priority;
use App\Enums\UseCase\RiskLevel;
use App\Enums\UseCase\ROIClassification;
use App\Enums\UseCase\Status;
use App\Models\Stakeholder;
use App\Models\UseCase;
use App\Models\Organization;
use App\Repositories\UseCaseRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UseCaseRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private UseCaseRepository $useCaseRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCaseRepository = new UseCaseRepository();
    }

    public function test_it_gets_filtered_use_cases(): void
    {
        $organization = Organization::factory()->create();
        UseCase::factory()->create(['name' => 'Test Use Case 1', 'status' => Status::ACTIVE->value, 'organization_id' => $organization->id]);
        UseCase::factory()->create(['name' => 'Another Use Case', 'status' => Status::SUSPENDED->value, 'organization_id' => $organization->id]);
        UseCase::factory()->create(['name' => 'Test Use Case 2', 'status' => Status::ACTIVE->value, 'organization_id' => $organization->id]);

        $filters = ['name' => 'Test', 'status' => Status::ACTIVE->value, 'per_page' => 2];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $useCase) {
            $this->assertStringContainsString('Test', $useCase->name);
            $this->assertEquals(Status::ACTIVE->value, $useCase->status);
        }
    }

    public function test_it_creates_use_case(): void
    {
        $organization = Organization::factory()->create();
        $businessOwner = Stakeholder::factory()->create();
        $technicalOwner = Stakeholder::factory()->create();

        $data = [
            'organization_id' => $organization->id,
            'name' => 'New AI Use Case',
            'description' => 'This is a detailed description of the new AI use case. It provides comprehensive information about what the use case aims to achieve and how it will be implemented in the organization.',
            'business_objective' => 'To improve operational efficiency and reduce costs by automating repetitive tasks.',
            'business_owner_id' => $businessOwner->id,
            'technical_owner_id' => $technicalOwner->id,
            'business_domain' => BusinessDomain::OPERATIONS->value,
            'roi_classification' => ROIClassification::HIGH->value,
            'priority' => Priority::HIGH->value,
            'risk_level' => RiskLevel::MEDIUM->value,
            'data_sensitivity' => DataSensitivity::CONFIDENTIAL->value,
            'expected_roi_percentage' => 25.50,
            'budget_allocated' => 100000.00,
            'target_go_live_date' => now()->addMonth(),
            'status' => Status::IN_DEVELOPMENT->value,
            'created_by' => 'creator@example.com',
            'updated_by' => 'updater@example.com',
            'roi_assessment' => true,
            'risk_assessment' => true,
            'data_assessment' => false,
            'estimated_implementation_cost' => 75000.00,
            'estimated_reduction_in_time' => 30.00,
            'estimated_reduction_in_cost' => 50000.00,
            'estimated_revenue_increase' => 150000.00,
            'estimated_fte_capacity_saving' => 5,
            'data_availability_status' => DataAvailabilityStatus::AVAILABLE->value,
            'data_readiness' => DataReadiness::D3->value,
        ];

        $useCase = $this->useCaseRepository->createUseCase($data);

        $this->assertInstanceOf(UseCase::class, $useCase);
        $this->assertEquals('New AI Use Case', $useCase->name);
        $this->assertStringContainsString('detailed description', $useCase->description);
        $this->assertEquals(Status::IN_DEVELOPMENT->value, $useCase->status);
        $this->assertEquals($businessOwner->id, $useCase->business_owner_id);
        $this->assertEquals($technicalOwner->id, $useCase->technical_owner_id);
        $this->assertDatabaseHas('use_cases', ['name' => 'New AI Use Case']);
    }

    public function test_it_gets_filtered_use_cases_with_no_filters(): void
    {
        UseCase::factory()->count(5)->create();

        $result = $this->useCaseRepository->getFilteredUseCases();

        $this->assertCount(5, $result->items());
    }

    public function test_it_creates_use_case_with_minimal_required_fields(): void
    {
        $data = [
            'organization_id' => Organization::factory()->create()->id,
            'name' => 'Minimal Use Case',
            'description' => 'This is a minimal but valid description that meets the minimum length requirement of 100 characters for testing purposes.',
            'business_objective' => 'To test minimal field creation with valid objective length.',
            'business_domain' => BusinessDomain::FINANCE->value,
            'data_availability_status' => DataAvailabilityStatus::NOT_AVAILABLE->value,
            'data_readiness' => DataReadiness::D1->value,
            'risk_level' => RiskLevel::LOW->value,
            'data_sensitivity' => DataSensitivity::INTERNAL->value,
            'status' => Status::DRAFT->value,
            'created_by' => 'test@example.com',
        ];

        $useCase = $this->useCaseRepository->createUseCase($data);

        $this->assertInstanceOf(UseCase::class, $useCase);
        $this->assertEquals('Minimal Use Case', $useCase->name);
        $this->assertNull($useCase->business_owner_id);
        $this->assertNull($useCase->technical_owner_id);
        $this->assertNull($useCase->budget_allocated);
    }

    public function test_it_filters_by_status(): void
    {
        UseCase::factory()->create(['status' => Status::DRAFT->value]);
        UseCase::factory()->create(['status' => Status::IN_DEVELOPMENT->value]);

        $filters = ['status' => Status::DRAFT->value];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(1, $result->items());
    }

    public function test_it_filters_by_organization_id(): void
    {
        $organization1 = Organization::factory()->create();
        $organization2 = Organization::factory()->create();

        UseCase::factory()->count(3)->create(['organization_id' => $organization1->id]);
        UseCase::factory()->count(2)->create(['organization_id' => $organization2->id]);

        $filters = ['organization_id' => $organization1->id];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(3, $result->items());
        foreach ($result->items() as $useCase) {
            $this->assertEquals($organization1->id, $useCase->organization_id);
        }
    }

    public function test_it_filters_by_risk_level(): void
    {
        UseCase::factory()->create(['risk_level' => RiskLevel::HIGH->value]);
        UseCase::factory()->create(['risk_level' => RiskLevel::LOW->value]);
        UseCase::factory()->create(['risk_level' => RiskLevel::HIGH->value]);

        $filters = ['risk_level' => RiskLevel::HIGH->value];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $useCase) {
            $this->assertEquals(RiskLevel::HIGH->value, $useCase->risk_level);
        }
    }

    public function test_it_filters_by_business_domain(): void
    {
        UseCase::factory()->create(['business_domain' => BusinessDomain::FINANCE->value]);
        UseCase::factory()->create(['business_domain' => BusinessDomain::OPERATIONS->value]);
        UseCase::factory()->create(['business_domain' => BusinessDomain::FINANCE->value]);

        $filters = ['business_domain' => BusinessDomain::FINANCE->value];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $useCase) {
            $this->assertEquals(BusinessDomain::FINANCE->value, $useCase->business_domain);
        }
    }

    public function test_it_filters_by_owner(): void
    {
        $businessOwner = Stakeholder::factory()->create(['display_name' => 'John Smith']);
        $technicalOwner = Stakeholder::factory()->create(['display_name' => 'Jane Doe']);
        $otherOwner = Stakeholder::factory()->create(['display_name' => 'Bob Wilson']);

        UseCase::factory()->create(['business_owner_id' => $businessOwner->id]);
        UseCase::factory()->create(['technical_owner_id' => $technicalOwner->id]);
        UseCase::factory()->create(['business_owner_id' => $otherOwner->id]);

        $filters = ['owner' => 'John'];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(1, $result->items());
    }

    public function test_it_filters_by_roi_assessment(): void
    {
        UseCase::factory()->create(['roi_assessment' => true]);
        UseCase::factory()->create(['roi_assessment' => false]);
        UseCase::factory()->create(['roi_assessment' => true]);

        $filters = ['roi_assessment' => true];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $useCase) {
            $this->assertTrue($useCase->roi_assessment);
        }
    }

    public function test_it_filters_by_risk_assessment(): void
    {
        UseCase::factory()->create(['risk_assessment' => true]);
        UseCase::factory()->create(['risk_assessment' => false]);
        UseCase::factory()->create(['risk_assessment' => true]);

        $filters = ['risk_assessment' => true];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $useCase) {
            $this->assertTrue($useCase->risk_assessment);
        }
    }

    public function test_it_filters_by_data_assessment(): void
    {
        UseCase::factory()->create(['data_assessment' => true]);
        UseCase::factory()->create(['data_assessment' => false]);
        UseCase::factory()->create(['data_assessment' => true]);

        $filters = ['data_assessment' => true];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $useCase) {
            $this->assertTrue($useCase->data_assessment);
        }
    }

    public function test_it_filters_by_date_range(): void
    {
        UseCase::factory()->create(['created_at' => now()->subDays(10)]);
        UseCase::factory()->create(['created_at' => now()->subDays(5)]);
        UseCase::factory()->create(['created_at' => now()->subDays(1)]);

        $filters = [
            'from' => now()->subDays(7)->format('Y-m-d'),
            'to' => now()->subDays(2)->format('Y-m-d'),
        ];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(1, $result->items());
    }

    public function test_it_filters_by_from_date_only(): void
    {
        UseCase::factory()->create(['created_at' => now()->subDays(10)]);
        UseCase::factory()->create(['created_at' => now()->subDays(5)]);
        UseCase::factory()->create(['created_at' => now()->subDays(1)]);

        $filters = ['from' => now()->subDays(6)->format('Y-m-d')];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_it_filters_by_to_date_only(): void
    {
        UseCase::factory()->create(['created_at' => now()->subDays(10)]);
        UseCase::factory()->create(['created_at' => now()->subDays(5)]);
        UseCase::factory()->create(['created_at' => now()->subDays(1)]);

        $filters = ['to' => now()->subDays(6)->format('Y-m-d')];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(1, $result->items());
    }

    public function test_it_filters_by_multiple_filters(): void
    {
        $organization = Organization::factory()->create();

        UseCase::factory()->create([
            'organization_id' => $organization->id,
            'status' => Status::ACTIVE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'business_domain' => BusinessDomain::FINANCE->value,
        ]);
        UseCase::factory()->create([
            'organization_id' => $organization->id,
            'status' => Status::DRAFT->value,
            'risk_level' => RiskLevel::HIGH->value,
            'business_domain' => BusinessDomain::FINANCE->value,
        ]);
        UseCase::factory()->create([
            'organization_id' => $organization->id,
            'status' => Status::ACTIVE->value,
            'risk_level' => RiskLevel::LOW->value,
            'business_domain' => BusinessDomain::OPERATIONS->value,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'status' => Status::ACTIVE->value,
            'risk_level' => RiskLevel::HIGH->value,
            'business_domain' => BusinessDomain::FINANCE->value,
        ];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(1, $result->items());
        $useCase = $result->items()[0];
        $this->assertEquals(Status::ACTIVE->value, $useCase->status);
        $this->assertEquals(RiskLevel::HIGH->value, $useCase->risk_level);
        $this->assertEquals(BusinessDomain::FINANCE->value, $useCase->business_domain);
    }
}

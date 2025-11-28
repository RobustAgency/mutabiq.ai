<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\UseCase;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Enums\UseCase\Status;
use App\Enums\UseCase\Priority;
use App\Enums\UseCase\RiskLevel;
use App\Enums\UseCase\DataReadiness;
use App\Enums\UseCase\BusinessDomain;
use App\Enums\UseCase\DataSensitivity;
use App\Repositories\UseCaseRepository;
use App\Enums\UseCase\ROIClassification;
use Illuminate\Foundation\Testing\WithFaker;
use App\Enums\UseCase\DataAvailabilityStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UseCaseRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private UseCaseRepository $useCaseRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCaseRepository = new UseCaseRepository;
    }

    public function test_it_gets_filtered_use_cases(): void
    {
        $organization = Organization::factory()->create();
        UseCase::factory()->create(['name' => 'Test Use Case 1', 'status' => Status::IN_PRODUCTION->value, 'organization_id' => $organization->id]);
        UseCase::factory()->create(['name' => 'Another Use Case', 'status' => Status::APPROVED->value, 'organization_id' => $organization->id]);
        UseCase::factory()->create(['name' => 'Test Use Case 2', 'status' => Status::IN_PRODUCTION->value, 'organization_id' => $organization->id]);

        $filters = ['name' => 'Test', 'status' => Status::IN_PRODUCTION->value, 'per_page' => 2];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $useCase) {
            $this->assertStringContainsString('Test', $useCase->name);
            $this->assertEquals(Status::IN_PRODUCTION->value, $useCase->status);
        }
    }

    public function test_it_creates_use_case(): void
    {
        $organization = Organization::factory()->create();
        $businessOwner = Stakeholder::factory()->create();
        $technicalOwner = Stakeholder::factory()->create();

        $user = User::factory()->create();

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
            'status' => Status::STAGING->value,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'roi_assessment' => true,
            'risk_assessment' => true,
            'data_assessment' => false,
            'estimated_implementation_cost' => 75000.00,
            'estimated_reduction_in_time' => 30.00,
            'estimated_reduction_in_cost' => 50000.00,
            'estimated_revenue_increase' => 150000.00,
            'estimated_fte_capacity_saving' => 5,
            'data_availability_status' => DataAvailabilityStatus::AVAILABLE->value,
            'data_readiness' => DataReadiness::REQUIRES_INTEGRATION->value,
        ];

        $useCase = $this->useCaseRepository->createUseCase($data);

        $this->assertInstanceOf(UseCase::class, $useCase);
        $this->assertEquals('New AI Use Case', $useCase->name);
        $this->assertStringContainsString('detailed description', $useCase->description);
        $this->assertEquals(Status::STAGING->value, $useCase->status);
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

    public function test_it_filters_by_status(): void
    {
        UseCase::factory()->create(['status' => Status::DRAFT->value]);
        UseCase::factory()->create(['status' => Status::STAGING->value]);

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

    public function test_it_filters_by_preliminary_risk_level(): void
    {
        UseCase::factory()->create(['preliminary_risk_level' => RiskLevel::HIGH->value]);
        UseCase::factory()->create(['preliminary_risk_level' => RiskLevel::LOW->value]);
        UseCase::factory()->create(['preliminary_risk_level' => RiskLevel::HIGH->value]);

        $filters = ['preliminary_risk_level' => RiskLevel::HIGH->value];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $useCase) {
            $this->assertEquals(RiskLevel::HIGH->value, $useCase->preliminary_risk_level);
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
            'status' => Status::STAGING->value,
            'preliminary_risk_level' => RiskLevel::HIGH->value,
            'business_domain' => BusinessDomain::FINANCE->value,
        ]);
        UseCase::factory()->create([
            'organization_id' => $organization->id,
            'status' => Status::DRAFT->value,
            'preliminary_risk_level' => RiskLevel::HIGH->value,
            'business_domain' => BusinessDomain::FINANCE->value,
        ]);
        UseCase::factory()->create([
            'organization_id' => $organization->id,
            'status' => Status::STAGING->value,
            'preliminary_risk_level' => RiskLevel::LOW->value,
            'business_domain' => BusinessDomain::OPERATIONS->value,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'status' => Status::STAGING->value,
            'preliminary_risk_level' => RiskLevel::HIGH->value,
            'business_domain' => BusinessDomain::FINANCE->value,
        ];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(1, $result->items());
    }
}

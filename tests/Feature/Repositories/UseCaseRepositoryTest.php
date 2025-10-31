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
        UseCase::factory()->create(['name' => 'Test Use Case 1', 'status' => Status::ACTIVE->value]);
        UseCase::factory()->create(['name' => 'Another Use Case', 'status' => Status::SUSPENDED->value]);
        UseCase::factory()->create(['name' => 'Test Use Case 2', 'status' => Status::ACTIVE->value]);

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
        $businessOwner = Stakeholder::factory()->create();
        $technicalOwner = Stakeholder::factory()->create();

        $data = [
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

    public function test_it_filters_by_name(): void
    {
        UseCase::factory()->create(['name' => 'Marketing Campaign']);
        UseCase::factory()->create(['name' => 'Finance Report']);
        UseCase::factory()->create(['name' => 'Marketing Analysis']);

        $filters = ['name' => 'Marketing'];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_it_filters_by_status(): void
    {
        UseCase::factory()->create(['status' => Status::DRAFT->value]);
        UseCase::factory()->create(['status' => Status::IN_DEVELOPMENT->value]);

        $filters = ['status' => Status::DRAFT->value];
        $result = $this->useCaseRepository->getFilteredUseCases($filters);

        $this->assertCount(1, $result->items());
    }
}

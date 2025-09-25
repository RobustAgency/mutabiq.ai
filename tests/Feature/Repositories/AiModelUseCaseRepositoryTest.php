<?php

namespace Tests\Feature\Repositories;

use App\Enums\AiModelUseCase\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Repositories\AiModelUseCaseRepository;
use App\Models\AiModelUseCase;
use App\Enums\AiModelUseCase\DataSensitivity;
use App\Enums\AiModelUseCase\RiskLevel;
use Tests\TestCase;

class AiModelUseCaseRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private AiModelUseCaseRepository $aiModelUseCaseRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiModelUseCaseRepository = new AiModelUseCaseRepository();
    }

    public function test_it_gets_filtered_ai_model_use_cases(): void
    {
        AiModelUseCase::factory()->create(['title' => 'Test Use Case 1', 'status' => 'active']);
        AiModelUseCase::factory()->create(['title' => 'Another Use Case', 'status' => 'inactive']);
        AiModelUseCase::factory()->create(['title' => 'Test Use Case 2', 'status' => 'active']);

        $filters = ['title' => 'Test', 'status' => 'active', 'per_page' => 2];
        $result = $this->aiModelUseCaseRepository->getFilteredAiModelUseCases($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $useCase) {
            $this->assertStringContainsString('Test', $useCase->title);
            $this->assertEquals('active', $useCase->status);
        }
    }

    public function test_it_creates_ai_model_use_case(): void
    {
        $data = [
            'title' => 'New Use Case',
            'description' => 'Description of the new use case',
            'status' => Status::IN_DEVELOPMENT,
            'business_domain' => 'IT',
            'business_owner_email' => 'owner@example.com',
            'technical_owner_email' => 'tech@example.com',
            'regulatory_scope' => 'GDPR',
            'data_sensitivity' => DataSensitivity::CONFIDENTIAL,
            'go_live_date' => now()->addMonth(),
            'expected_roi' => 20.5,
            'implementation_cost' => 10000,
            'reduction_in_time' => 30.0,
            'reduction_in_cost' => 5000,
            'increase_in_revenue' => 10000,
            'risk_avoidance' => 2000,
            'fte_capacity_saved' => 1,
            'use_case_type' => 'Automation',
            'value_driver' => 'Efficiency',
            'risk_level' => RiskLevel::MEDIUM,
            'overall_risk_score' => 5,
            'human_oversight_mode' => 'Manual',
            'dpia' => true,
            'aia' => false,
            'data_availability_status' => 'Available',
            'data_readiness_level' => 'Ready',
            'data_freshness' => 'Fresh',
        ];

        $useCase = $this->aiModelUseCaseRepository->createAiModelUseCase($data);

        $this->assertInstanceOf(AiModelUseCase::class, $useCase);
        $this->assertEquals('New Use Case', $useCase->title);
        $this->assertEquals('Description of the new use case', $useCase->description);
        $this->assertEquals(Status::IN_DEVELOPMENT, $useCase->status);
        $this->assertDatabaseHas('ai_model_use_cases', ['title' => 'New Use Case']);
    }

    public function test_it_gets_filtered_ai_model_use_cases_with_no_filters(): void
    {
        AiModelUseCase::factory()->count(5)->create();

        $result = $this->aiModelUseCaseRepository->getFilteredAiModelUseCases();

        $this->assertCount(5, $result->items());
    }
}

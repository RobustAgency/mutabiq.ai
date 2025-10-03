<?php

namespace Tests\Feature\Controllers\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\AiModelUseCase;
use App\Enums\AiModelUseCase\Status;
use App\Enums\AiModelUseCase\DataSensitivity;
use App\Enums\AiModelUseCase\RegulatoryScope;
use App\Enums\AiModelUseCase\RiskLevel;
use Tests\TestCase;

class AiModelUseCaseControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_get_ai_model_use_cases(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        AiModelUseCase::factory()->count(3)->create();

        $response = $this->getJson('/api/ai-model-use-cases?per_page=2');
        $response->assertOk();
        $response->assertJsonCount(2, 'data.data');
    }

    public function test_user_can_create_ai_model_use_case(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'title' => 'New Use Case',
            'description' => 'Description of the new use case',
            'status' => Status::IN_DEVELOPMENT,
            'business_domain' => 'IT',
            'business_owner_email' => 'owner@example.com',
            'technical_owner_email' => 'tech@example.com',
            'regulatory_scope' => [RegulatoryScope::GDPR, RegulatoryScope::CCPA],
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


        $response = $this->postJson('/api/ai-model-use-cases', $data);
        $response->assertCreated();

        $response->assertJsonFragment([
            'error' => false,
            'message' => 'AI Model Use Case created successfully',
        ]);

        $this->assertDatabaseHas('ai_model_use_cases', [
            'title' => 'New Use Case',
            'description' => 'Description of the new use case',
            'status' => Status::IN_DEVELOPMENT,
        ]);
    }

    public function test_user_can_get_specific_ai_model_use_case(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $useCase = AiModelUseCase::factory()->create([
            'title' => 'Specific Use Case',
            'description' => 'Details of the specific use case.',
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/ai-model-use-cases/{$useCase->id}");
        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $useCase->id,
            'title' => 'Specific Use Case',
            'description' => 'Details of the specific use case.',
            'status' => 'active',
            'error' => false,
            'message' => 'AI Model Use Case retrieved successfully',
        ]);
    }
}

<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\UseCase;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Enums\UseCase\Status;
use App\Enums\UseCase\RiskLevel;
use App\Enums\UseCase\DataReadiness;
use App\Enums\UseCase\BusinessDomain;
use App\Enums\UseCase\HumanOversight;
use App\Enums\UseCase\DataSensitivity;
use App\Enums\UseCase\ROIClassification;
use Illuminate\Foundation\Testing\WithFaker;
use App\Enums\UseCase\DataAvailabilityStatus;
use App\Enums\CorrectivePreventiveAction\Priority;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UseCaseControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
    }

    public function test_user_can_get_use_cases(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->actingAs($user);

        UseCase::factory()->count(3)->create(['organization_id' => $this->organization->id]);

        $response = $this->getJson('/api/use-cases?per_page=2');
        $response->assertOk();
        $response->assertJsonCount(2, 'data.data');
    }

    public function test_user_can_create_use_case(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $stakeholder = Stakeholder::factory()->create(['organization_id' => $this->organization->id]);
        $this->actingAs($user);

        $businessOwner = Stakeholder::factory()->create(['organization_id' => $this->organization->id]);
        $technicalOwner = Stakeholder::factory()->create(['organization_id' => $this->organization->id]);

        $data = [
            'name' => 'New AI Use Case',
            'description' => 'This is a detailed description of the new AI use case. This is a detailed description of the new AI use case.',
            'problem_statement' => 'The current process is inefficient and error-prone. This is a detailed description of the new AI use case.',
            'expected_business_value' => 'Significant cost savings and efficiency improvements. This is a detailed description of the new AI use case.',
            'stakeholder_ids' => [$stakeholder->id],
            'status' => Status::STAGING->value,
            'business_domain' => BusinessDomain::OPERATIONS->value,
            'roi_classification' => ROIClassification::HIGH->value,
            'priority' => Priority::HIGH->value,
            'data_sensitivity' => DataSensitivity::CONFIDENTIAL->value,
            'expected_roi' => 150.00,
            'estimated_time_savings' => 120.50,
            'estimated_cost_savings' => 100.00,
            'estimated_revenue_impact' => 200.00,
            'success_metrics' => 'Increased efficiency and reduced costs. This is a detailed description of the new AI use case. This is a detailed description of the new AI use case.',
            'preliminary_risk_level' => RiskLevel::MEDIUM->value,
            'regulatory_impact' => 'yes',
            'potential_harm' => 'Minimal. Increased efficiency and reduced costs. This is a detailed description of the new AI use case.',
            'human_oversight_mode' => HumanOversight::HUMAN_IN_THE_LOOP->value,
            'dependencies' => 'None',
            'budget_allocated' => 50000,
            'target_deployment_date' => '2023-12-31',
            'estimated_fte_saving' => 2,
            'data_availability_status' => DataAvailabilityStatus::AVAILABLE->value,
            'data_readiness' => DataReadiness::READY_FOR_USE->value,
            'business_owner_id' => $businessOwner->id,
            'technical_owner_id' => $technicalOwner->id,
        ];

        $response = $this->postJson('/api/use-cases', $data);
        $response->assertCreated();

        $response->assertJsonFragment([
            'error' => false,
            'message' => 'Use Case created successfully',
        ]);

        $this->assertDatabaseHas('use_cases', [
            'name' => 'New AI Use Case',
            'status' => Status::STAGING->value,
            'business_domain' => BusinessDomain::OPERATIONS->value,
        ]);

        $this->assertDatabaseHas('stakeholder_use_case', [
            'stakeholder_id' => $stakeholder->id,
        ]);
    }

    public function test_user_can_get_specific_use_case(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->actingAs($user);

        $useCase = UseCase::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Specific Use Case',
            'status' => Status::IN_PRODUCTION->value,
        ]);

        $response = $this->getJson("/api/use-cases/{$useCase->id}");
        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $useCase->id,
            'name' => 'Specific Use Case',
            'status' => Status::IN_PRODUCTION->value,
            'error' => false,
            'message' => 'Use Case retrieved successfully',
        ]);
    }

    public function test_user_cannot_create_use_case_with_short_name(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->actingAs($user);

        $data = [
            'name' => 'AI', // Too short (min 5 characters)
            'description' => 'This is a detailed description of the new AI use case. It provides comprehensive information about what the use case aims to achieve.',
            'business_objective' => 'To improve operational efficiency and reduce costs.',
            'business_domain' => BusinessDomain::OPERATIONS->value,
            'risk_level' => RiskLevel::MEDIUM->value,
            'data_sensitivity' => DataSensitivity::CONFIDENTIAL->value,
            'status' => Status::DRAFT->value,
            'created_by' => 'test@example.com',
        ];

        $response = $this->postJson('/api/use-cases', $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_user_cannot_create_use_case_with_short_description(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->actingAs($user);

        $data = [
            'name' => 'Valid Use Case Name',
            'description' => 'Too short', // Less than 100 characters
            'business_objective' => 'To improve operational efficiency and reduce costs.',
            'business_domain' => BusinessDomain::OPERATIONS->value,
            'risk_level' => RiskLevel::MEDIUM->value,
            'data_sensitivity' => DataSensitivity::CONFIDENTIAL->value,
            'status' => Status::DRAFT->value,
            'created_by' => 'test@example.com',
        ];

        $response = $this->postJson('/api/use-cases', $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    public function test_user_cannot_create_use_case_with_invalid_stakeholder_id(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->actingAs($user);

        $data = [
            'name' => 'Valid Use Case Name',
            'description' => 'This is a detailed description of the new AI use case. It provides comprehensive information about what the use case aims to achieve.',
            'business_objective' => 'To improve operational efficiency and reduce costs.',
            'business_owner_id' => 99999, // Non-existent stakeholder
            'business_domain' => BusinessDomain::OPERATIONS->value,
            'risk_level' => RiskLevel::MEDIUM->value,
            'data_sensitivity' => DataSensitivity::CONFIDENTIAL->value,
            'status' => Status::DRAFT->value,
            'created_by' => 'test@example.com',
        ];

        $response = $this->postJson('/api/use-cases', $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['business_owner_id']);
    }

    public function test_user_cannot_create_use_case_with_invalid_enum_values(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->actingAs($user);

        $data = [
            'name' => 'Use Case with Invalid Enums',
            'description' => 'This is a detailed description of the new AI use case. It provides comprehensive information about what the use case aims to achieve.',
            'business_objective' => 'To improve operational efficiency and reduce costs.',
            'business_domain' => 'InvalidDomain', // Invalid enum
            'risk_level' => RiskLevel::MEDIUM->value,
            'data_sensitivity' => DataSensitivity::CONFIDENTIAL->value,
            'status' => Status::DRAFT->value,
            'created_by' => 'test@example.com',
        ];

        $response = $this->postJson('/api/use-cases', $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['business_domain']);
    }

    public function test_user_cannot_create_use_case_with_negative_budget(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->actingAs($user);

        $data = [
            'name' => 'Use Case with Negative Budget',
            'description' => 'This is a detailed description of the new AI use case. It provides comprehensive information about what the use case aims to achieve.',
            'business_objective' => 'To improve operational efficiency and reduce costs.',
            'business_domain' => BusinessDomain::OPERATIONS->value,
            'risk_level' => RiskLevel::MEDIUM->value,
            'data_sensitivity' => DataSensitivity::CONFIDENTIAL->value,
            'budget_allocated' => -1000, // Negative value
            'status' => Status::DRAFT->value,
            'created_by' => 'test@example.com',
        ];

        $response = $this->postJson('/api/use-cases', $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['budget_allocated']);
    }

    public function test_user_can_filter_use_cases_by_status(): void
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->actingAs($user);

        UseCase::factory()->create(['organization_id' => $this->organization->id, 'status' => Status::IN_PRODUCTION->value]);
        UseCase::factory()->create(['organization_id' => $this->organization->id, 'status' => Status::STAGING->value]);
        UseCase::factory()->create(['organization_id' => $this->organization->id, 'status' => Status::IN_PRODUCTION->value]);

        $response = $this->getJson('/api/use-cases?status='.Status::IN_PRODUCTION->value);
        $response->assertOk();
        $response->assertJsonCount(2, 'data.data');
    }

    public function test_unauthenticated_user_cannot_access_use_cases(): void
    {
        $response = $this->getJson('/api/use-cases');
        $response->assertUnauthorized();
    }
}

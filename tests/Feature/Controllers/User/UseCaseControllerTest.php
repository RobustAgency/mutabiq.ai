<?php

namespace Tests\Feature\Controllers\User;

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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UseCaseControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_get_use_cases(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        UseCase::factory()->count(3)->create();

        $response = $this->getJson('/api/use-cases?per_page=2');
        $response->assertOk();
        $response->assertJsonCount(2, 'data.data');
    }

    public function test_user_can_create_use_case(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

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
            'target_go_live_date' => now()->addMonth()->format('Y-m-d'),
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

        $response = $this->postJson('/api/use-cases', $data);
        $response->assertCreated();

        $response->assertJsonFragment([
            'error' => false,
            'message' => 'Use Case created successfully',
        ]);

        $this->assertDatabaseHas('use_cases', [
            'name' => 'New AI Use Case',
            'status' => Status::IN_DEVELOPMENT->value,
            'business_domain' => BusinessDomain::OPERATIONS->value,
        ]);
    }

    public function test_user_can_get_specific_use_case(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $useCase = UseCase::factory()->create([
            'name' => 'Specific Use Case',
            'status' => Status::ACTIVE->value,
        ]);

        $response = $this->getJson("/api/use-cases/{$useCase->id}");
        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $useCase->id,
            'name' => 'Specific Use Case',
            'status' => Status::ACTIVE->value,
            'error' => false,
            'message' => 'Use Case retrieved successfully',
        ]);
    }

    public function test_user_cannot_create_use_case_with_short_name(): void
    {
        $user = User::factory()->create();
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
        $user = User::factory()->create();
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
        $user = User::factory()->create();
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
        $user = User::factory()->create();
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
        $user = User::factory()->create();
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
        $user = User::factory()->create();
        $this->actingAs($user);

        UseCase::factory()->create(['status' => Status::ACTIVE->value]);
        UseCase::factory()->create(['status' => Status::DRAFT->value]);
        UseCase::factory()->create(['status' => Status::ACTIVE->value]);

        $response = $this->getJson('/api/use-cases?status=' . Status::ACTIVE->value);
        $response->assertOk();
        $response->assertJsonCount(2, 'data.data');
    }

    public function test_unauthenticated_user_cannot_access_use_cases(): void
    {
        $response = $this->getJson('/api/use-cases');
        $response->assertUnauthorized();
    }
}

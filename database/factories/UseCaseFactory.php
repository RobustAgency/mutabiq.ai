<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Enums\UseCase\Status;
use App\Enums\UseCase\Priority;
use App\Enums\UseCase\RiskLevel;
use App\Enums\UseCase\DataReadiness;
use App\Enums\UseCase\BusinessDomain;
use App\Enums\UseCase\DataSensitivity;
use App\Enums\UseCase\ROIClassification;
use App\Enums\UseCase\DataAvailabilityStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UseCase>
 */
class UseCaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraphs(3, true),
            'problem_statement' => $this->faker->paragraph(),
            'expected_business_value' => $this->faker->paragraph(),
            'business_owner_id' => Stakeholder::factory(),
            'technical_owner_id' => Stakeholder::factory(),
            'business_domain' => $this->faker->randomElement(BusinessDomain::cases())->value,
            'roi_classification' => $this->faker->randomElement(ROIClassification::cases())->value,
            'priority' => $this->faker->randomElement(Priority::cases())->value,
            'data_sensitivity' => $this->faker->randomElement(DataSensitivity::cases())->value,
            'expected_roi' => $this->faker->randomFloat(2, 0, 999.99),
            'budget_allocated' => $this->faker->randomFloat(2, 10000, 1000000),
            'target_deployment_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'status' => $this->faker->randomElement(Status::cases())->value,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'estimated_time_savings' => $this->faker->randomFloat(2, 0, 99.99),
            'estimated_cost_savings' => $this->faker->randomFloat(2, 10000, 100000),
            'estimated_revenue_impact' => $this->faker->randomFloat(2, 50000, 500000),
            'estimated_fte_saving' => $this->faker->numberBetween(1, 50),
            'data_availability_status' => $this->faker->randomElement(DataAvailabilityStatus::cases())->value,
            'data_readiness' => $this->faker->randomElement(DataReadiness::cases())->value,
            'success_metrics' => $this->faker->sentence(),
            'preliminary_risk_level' => $this->faker->randomElement(RiskLevel::cases())->value,
            'regulatory_impact' => $this->faker->sentence(),
            'potential_harm' => $this->faker->sentence(),
            'human_oversight_mode' => $this->faker->sentence(),
            'dependencies' => $this->faker->sentence(),
        ];
    }
}

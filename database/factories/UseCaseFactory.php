<?php

namespace Database\Factories;

use App\Enums\UseCase\BusinessDomain;
use App\Enums\UseCase\DataAvailabilityStatus;
use App\Enums\UseCase\DataReadiness;
use App\Enums\UseCase\DataSensitivity;
use App\Enums\UseCase\Priority;
use App\Enums\UseCase\RiskLevel;
use App\Enums\UseCase\ROIClassification;
use App\Enums\UseCase\Status;
use App\Models\Stakeholder;
use App\Models\Organization;
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
            'business_objective' => $this->faker->paragraphs(2, true),
            'business_owner_id' => Stakeholder::factory(),
            'technical_owner_id' => Stakeholder::factory(),
            'business_domain' => $this->faker->randomElement(BusinessDomain::cases())->value,
            'roi_classification' => $this->faker->randomElement(ROIClassification::cases())->value,
            'priority' => $this->faker->randomElement(Priority::cases())->value,
            'risk_level' => $this->faker->randomElement(RiskLevel::cases())->value,
            'data_sensitivity' => $this->faker->randomElement(DataSensitivity::cases())->value,
            'expected_roi_percentage' => $this->faker->randomFloat(2, 0, 999.99),
            'budget_allocated' => $this->faker->randomFloat(2, 10000, 1000000),
            'target_go_live_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'status' => $this->faker->randomElement(Status::cases())->value,
            'created_by' => $this->faker->safeEmail,
            'updated_by' => $this->faker->safeEmail,
            'roi_assessment' => $this->faker->boolean,
            'risk_assessment' => $this->faker->boolean,
            'data_assessment' => $this->faker->boolean,
            'estimated_implementation_cost' => $this->faker->randomFloat(2, 50000, 500000),
            'estimated_reduction_in_time' => $this->faker->randomFloat(2, 0, 99.99),
            'estimated_reduction_in_cost' => $this->faker->randomFloat(2, 10000, 100000),
            'estimated_revenue_increase' => $this->faker->randomFloat(2, 50000, 500000),
            'estimated_fte_capacity_saving' => $this->faker->numberBetween(1, 50),
            'data_availability_status' => $this->faker->randomElement(DataAvailabilityStatus::cases())->value,
            'data_readiness' => $this->faker->randomElement(DataReadiness::cases())->value,
        ];
    }
}

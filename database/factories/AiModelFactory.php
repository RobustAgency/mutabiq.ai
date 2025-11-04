<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Organization;
use App\Models\Stakeholder;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\PrimaryCategory;
use App\Enums\OperationalStatus;
use App\Enums\BusinessStatus;
use App\Enums\OwnershipType;
use App\Enums\DevelopmentSource;
use App\Enums\OrganizationalRole;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiModel>
 */
class AiModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->paragraph(),
            'organization_id' => Organization::factory(),
            'source_org_stakeholder_id' => Stakeholder::factory(),
            'owner_stakeholder_id' => Stakeholder::factory(),
            'organizational_role' => $this->faker->randomElement(array_map(fn($c) => $c->value, OrganizationalRole::cases())),
            'vendor_id' => $this->faker->optional()->randomElement([null, Vendor::factory()]),
            'primary_category' => $this->faker->randomElement(array_map(fn($c) => $c->value, PrimaryCategory::cases())),
            'type' => $this->faker->randomElement(['classification', 'regression', 'generation', 'nlp', 'recommendation', 'computer_vision', 'time_series']),
            'domain_specialization' => $this->faker->randomElement(['fraud_detection', 'customer_insights', 'risk_scoring', 'content_moderation', 'pricing', 'forecasting']),
            'operational_status' => $this->faker->randomElement(array_map(fn($c) => $c->value, OperationalStatus::cases())),
            'business_status' => $this->faker->randomElement(array_map(fn($c) => $c->value, BusinessStatus::cases())),
            'regulatory_risk_classification' => $this->faker->randomElement(['low', 'moderate', 'high', 'critical']),
            'ownership_type' => $this->faker->randomElement(array_map(fn($c) => $c->value, OwnershipType::cases())),
            'development_source' => $this->faker->randomElement(array_map(fn($c) => $c->value, DevelopmentSource::cases())),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
            'creator_email' => $this->faker->safeEmail(),
        ];
    }
}

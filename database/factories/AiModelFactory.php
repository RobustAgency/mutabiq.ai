<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\OwnershipType;
use App\Models\Organization;
use App\Enums\BusinessStatus;
use App\Enums\PrimaryCategory;
use App\Enums\OrganizationalRole;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'organization_id' => Organization::factory(),
            'category' => $this->faker->randomElement(array_map(fn ($c) => $c->value, PrimaryCategory::cases())),
            'type' => $this->faker->randomElement(['classification', 'regression', 'generation', 'nlp', 'recommendation', 'computer_vision', 'time_series']),
            'technical_domain' => $this->faker->optional()->randomElement(['fraud_detection', 'customer_insights', 'risk_scoring', 'content_moderation', 'pricing', 'forecasting']),
            'purpose' => $this->faker->optional()->sentence(),
            'criticality_level' => $this->faker->optional()->randomElement(['low', 'medium', 'high', 'critical']),
            'business_adoption_status' => $this->faker->optional()->randomElement(array_map(fn ($c) => $c->value, BusinessStatus::cases())),
            'regulatory_risk_tier' => $this->faker->optional()->randomElement(['low', 'moderate', 'high', 'critical']),
            'eu_ai_category' => $this->faker->optional()->randomElement(['minimal', 'limited', 'high', 'unacceptable']),
            'ownership_category' => $this->faker->randomElement(array_map(fn ($c) => $c->value, OwnershipType::cases())),
            'responsible_org_role' => $this->faker->randomElement(array_map(fn ($c) => $c->value, OrganizationalRole::cases())),
            'business_owner_id' => $this->faker->optional()->randomElement([null, User::factory()]),
            'custodian_id' => $this->faker->optional()->randomElement([null, User::factory()]),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}

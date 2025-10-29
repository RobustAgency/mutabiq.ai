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
use App\Enums\StrategicImportance;
use App\Enums\OrganizationalRole;
use App\Enums\OwnershipType;
use App\Enums\DevelopmentSource;

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
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->optional()->paragraph(),
            'organization_id' => Organization::factory(),
            'source_organization_id' => Stakeholder::factory()->create(['type' => 'vendor_org']),
            'custodian_id' => Stakeholder::factory()->create(['type' => 'person']),
            'vendor_id' => Vendor::factory(),
            'primary_category' => $this->faker->randomElement(array_map(fn($c) => $c->value, PrimaryCategory::cases())),
            'type' => $this->faker->randomElement(['classification', 'regression', 'generation', 'nlp', 'recommendation']),
            'domain_specialization' => $this->faker->randomElement(['fraud_detection', 'customer_insights', 'risk_scoring', 'content_moderation', 'pricing']),
            'operational_status' => $this->faker->randomElement(array_map(fn($c) => $c->value, OperationalStatus::cases())),
            'business_status' => $this->faker->randomElement(array_map(fn($c) => $c->value, BusinessStatus::cases())),
            'total_versions' => $this->faker->numberBetween(1, 8),
            'strategic_importance' => $this->faker->randomElement(array_map(fn($c) => $c->value, StrategicImportance::cases())),
            'regulatory_risk_classification' => $this->faker->randomElement(['low', 'moderate', 'high', 'critical']),
            'organizational_role' => $this->faker->randomElement(array_map(fn($c) => $c->value, OrganizationalRole::cases())),
            'ownership_type' => $this->faker->randomElement(array_map(fn($c) => $c->value, OwnershipType::cases())),
            'current_owner' => $this->faker->userName(),
            'development_source' => $this->faker->randomElement(array_map(fn($c) => $c->value, DevelopmentSource::cases())),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}

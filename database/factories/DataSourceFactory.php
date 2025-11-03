<?php

namespace Database\Factories;

use App\Enums\DataSource\AccessMethod;
use App\Enums\DataSource\CloudProvider;
use App\Enums\DataSource\DataClassification;
use App\Enums\DataSource\DataResidency;
use App\Enums\DataSource\HostingModel;
use App\Enums\DataSource\ServiceModel;
use App\Enums\DataSource\SystemType;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DataSource>
 */
class DataSourceFactory extends Factory
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
            'name' => fake()->company() . ' ' . fake()->randomElement(['Database', 'Data Lake', 'API', 'Storage']),
            'system_type' => fake()->randomElement(SystemType::cases()),
            'owner_team' => fake()->randomElement(['Engineering', 'Data Science', 'Analytics', 'Operations', 'Product']),
            'data_domains' => fake()->randomElements(['Customer', 'Finance', 'Operations', 'Marketing', 'HR', 'Sales', 'Analytics'], fake()->numberBetween(1, 3)),
            'access_method' => fake()->randomElement(AccessMethod::cases()),
            'residency' => fake()->randomElement(DataResidency::cases()),
            'classification' => fake()->randomElement(DataClassification::cases()),
            'hosting_model' => fake()->randomElement(HostingModel::cases()),
            'service_model' => fake()->randomElement(ServiceModel::cases()),
            'cloud_provider' => fake()->randomElement(CloudProvider::cases()),
            'primary_region' => fake()->optional()->randomElement(['us-east-1', 'eu-west-1', 'me-south-1', 'ap-southeast-1']),
            'secondary_region' => fake()->optional()->randomElement(['us-west-2', 'eu-central-1', 'me-central-1', 'ap-northeast-1']),
            'network_ref' => fake()->optional()->regexify('vpc-[a-z0-9]{8}'),
            'retention_policy_ref' => fake()->optional()->randomElement(['policy-7d', 'policy-30d', 'policy-90d', 'policy-1y', 'policy-7y']),
            'catalog_uri' => fake()->optional()->url(),
        ];
    }
}

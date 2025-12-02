<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\KriIndicator;
use App\Models\Organization;
use App\Models\AiRiskRegister;
use App\Enums\KriIndicator\Status;
use App\Enums\KriIndicator\Frequency;
use App\Enums\KriIndicator\AlertRouting;
use App\Enums\KriIndicator\ActionOnBreach;
use App\Enums\KriIndicator\Directionality;
use App\Enums\KriIndicator\CollectionMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class KriIndicatorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = KriIndicator::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'ai_risk_register_id' => AiRiskRegister::factory(),
            'name' => $this->faker->words(3, true),
            'definition' => $this->faker->sentence(10),
            'directionality' => $this->faker->randomElement(Directionality::cases())->value,
            'unit' => $this->faker->randomElement(['percentage', 'count', 'ratio', 'score']),
            'sample_window' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'threshold_warning' => $this->faker->numberBetween(50, 80),
            'threshold_critical' => $this->faker->numberBetween(81, 99),
            'data_source' => $this->faker->randomElement(['database', 'api', 'log_aggregation', 'manual']),
            'collection_method' => $this->faker->randomElement(CollectionMethod::cases())->value,
            'frequency' => $this->faker->randomElement(Frequency::cases())->value,
            'alert_routing' => [$this->faker->randomElement(AlertRouting::cases())->value],
            'action_on_breach' => $this->faker->randomElement(ActionOnBreach::cases())->value,
            'status' => $this->faker->randomElement(Status::cases())->value,
            'owner_team' => $this->faker->randomElement(['Risk Team', 'Security Team', 'Product Team', 'ML Operations']),
            'notes' => $this->faker->optional(0.5)->sentence(8),
            'created_by' => User::factory(),
        ];
    }
}

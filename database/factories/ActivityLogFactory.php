<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Organization;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
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
            'user_id' => User::factory(),
            'actable_type' => 'App\\Models\\AiIncident',
            'actable_id' => 1,
            'action' => ActivityAction::CREATE->value,
            'description' => $this->faker->sentence(),
            'changes' => [],
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }

    /**
     * State for CREATE action.
     */
    public function actionCreate(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => ActivityAction::CREATE->value,
            'description' => 'Record created',
        ]);
    }

    /**
     * State for UPDATE action.
     */
    public function actionUpdate(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => ActivityAction::UPDATE->value,
            'description' => 'Record updated',
        ]);
    }

    /**
     * State for DELETE action.
     */
    public function actionDelete(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => ActivityAction::DELETE->value,
            'description' => 'Record deleted',
        ]);
    }
}

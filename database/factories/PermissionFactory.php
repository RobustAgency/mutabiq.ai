<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Permission>
     */
    protected $model = Permission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word().'.'.$this->faker->word().'.'.$this->faker->randomElement(['view', 'create', 'edit', 'delete']),
            'guard_name' => 'supabase',
        ];
    }

    /**
     * Indicate that the permission is for viewing.
     */
    public function view(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->word().'.'.$this->faker->word().'.view',
        ]);
    }

    /**
     * Indicate that the permission is for creating.
     */
    public function createAction(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->word().'.'.$this->faker->word().'.create',
        ]);
    }

    /**
     * Indicate that the permission is for editing.
     */
    public function edit(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->word().'.'.$this->faker->word().'.edit',
        ]);
    }

    /**
     * Indicate that the permission is for deleting.
     */
    public function delete(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->word().'.'.$this->faker->word().'.delete',
        ]);
    }

    /**
     * Indicate that the permission should use the web guard.
     */
    public function webGuard(): static
    {
        return $this->state(fn (array $attributes) => [
            'guard_name' => 'web',
        ]);
    }

    /**
     * Indicate that the permission should use the api guard.
     */
    public function apiGuard(): static
    {
        return $this->state(fn (array $attributes) => [
            'guard_name' => 'api',
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Role>
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word().'_'.$this->faker->randomNumber(5),
            'guard_name' => 'supabase',
        ];
    }

    /**
     * Indicate that the role is an admin role.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'admin',
        ]);
    }

    /**
     * Indicate that the role is a contributor role.
     */
    public function contributor(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'contributor',
        ]);
    }

    /**
     * Indicate that the role is a reviewer role.
     */
    public function reviewer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'reviewer',
        ]);
    }

    /**
     * Indicate that the role is a viewer role.
     */
    public function viewer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'viewer',
        ]);
    }

    /**
     * Indicate that the role should use the web guard.
     */
    public function webGuard(): static
    {
        return $this->state(fn (array $attributes) => [
            'guard_name' => 'web',
        ]);
    }

    /**
     * Indicate that the role should use the api guard.
     */
    public function apiGuard(): static
    {
        return $this->state(fn (array $attributes) => [
            'guard_name' => 'api',
        ]);
    }
}

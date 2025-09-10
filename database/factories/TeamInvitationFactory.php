<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Str;
use App\Models\Organization;
use App\Enums\InvitationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamInvitation>
 */
class TeamInvitationFactory extends Factory
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
            'invited_by' => User::factory(),
            'email' => $this->faker->unique()->safeEmail(),
            'role' => UserRole::CONTRIBUTOR,
            'token' => Str::random(16),
            'status' => InvitationStatus::PENDING,
            'expires_at' => now()->addDays(7),
        ];
    }
}

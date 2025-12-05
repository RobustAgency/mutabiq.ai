<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\AiModel;
use App\Models\Control;
use App\Models\Requirement;
use App\Enums\RequirementControl\Coverage;
use App\Enums\RequirementControl\ReviewStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequirementControl>
 */
class RequirementControlFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requirement_id' => Requirement::factory(),
            'control_id' => Control::factory(),
            'ai_model_id' => AiModel::factory(),
            'coverage' => $this->faker->randomElement(Coverage::cases())->value,
            'interpretation_notes' => $this->faker->paragraphs(2, true),
            'residual_gaps' => $this->faker->paragraphs(2, true),
            'review_status' => $this->faker->randomElement(ReviewStatus::cases())->value,
            'reviewed_by' => User::factory(),
            'reviewed_at' => $this->faker->dateTime(),
        ];
    }
}

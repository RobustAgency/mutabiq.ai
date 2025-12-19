<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\AiModel;
use App\Models\Control;
use App\Models\Project;
use App\Models\Requirement;
use App\Enums\ComplianceEvidence\ArtifactType;
use App\Enums\ComplianceEvidence\ReviewOutcome;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ComplianceEvidence>
 */
class ComplianceEvidenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'control_id' => Control::factory(),
            'requirement_id' => Requirement::factory(),
            'ai_model_id' => AiModel::factory(),
            'artifact_type' => $this->faker->randomElement(ArtifactType::cases())->value,
            'artifact_uri' => $this->faker->url(),
            'sample_ids' => json_encode($this->faker->words(3)),
            'sampling_method' => $this->faker->word(),
            'collection_period_start' => $this->faker->dateTime(),
            'collection_period_end' => $this->faker->dateTime(),
            'collected_by' => User::factory(),
            'review_outcome' => ReviewOutcome::PASS->value,
            'reviewed_by' => User::factory(),
            'reviewed_at' => $this->faker->dateTime(),
            'hash_checksum' => $this->faker->word(),
        ];
    }
}

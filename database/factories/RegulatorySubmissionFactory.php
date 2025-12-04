<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\AiModel;
use App\Models\Framework;
use App\Enums\RegulatorySubmission\Status;
use App\Enums\RegulatorySubmission\SubmissionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegulatorySubmission>
 */
class RegulatorySubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'framework_id' => Framework::factory(),
            'ai_model_id' => AiModel::factory(),
            'authority' => $this->faker->company(),
            'jurisdiction' => json_encode([$this->faker->countryCode()]),
            'submission_type' => $this->faker->randomElement(SubmissionType::cases())->value,
            'content_summary' => $this->faker->paragraphs(2, true),
            'tracking_id' => $this->faker->unique()->bothify('REG-####-????'),
            'status' => Status::DRAFT->value,
            'submitted_at' => now(),
            'commitments' => json_encode([
                'commitment_1' => $this->faker->sentence(),
                'commitment_2' => $this->faker->sentence(),
            ]),
            'renewal_due_at' => $this->faker->dateTime(),
            'evidence_bundle_ids' => json_encode([$this->faker->randomNumber()]),
            'submitted_by' => User::factory(),
            'documents_uri' => $this->faker->url(),
        ];
    }
}

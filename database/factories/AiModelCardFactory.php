<?php

namespace Database\Factories;

use App\Enums\CardFormat;
use App\Enums\CreatorRole;
use App\Enums\Status;
use App\Enums\PublicationStatus;
use App\Models\AiModelVersion;
use App\Models\Stakeholder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiModelCard>
 */
class AiModelCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'version_id' => AiModelVersion::factory(),
            'title' => $this->faker->sentence(4),
            'creator_role' => $this->faker->randomElement(CreatorRole::cases())->value,
            'owner_stakeholder_id' => Stakeholder::factory(),
            'format' => $this->faker->randomElement(CardFormat::cases())->value,
            'model_overview' => $this->faker->paragraph(3),
            'intended_use' => $this->faker->paragraph(3),
            'training_data_overview' => $this->faker->paragraph(5),
            'bias_evaluation_methods' => $this->faker->paragraph(3),
            'model_limitations' => $this->faker->paragraph(3),
            'ethical_considerations' => $this->faker->paragraph(3),
            'organizational_context' => [
                'department' => $this->faker->word,
                'contacts' => [
                    'primary' => $this->faker->email,
                    'secondary' => $this->faker->email,
                ],
                'usage' => $this->faker->sentence,
            ],
            'performance_summary' => $this->faker->paragraph(2),
            'risk_summary' => $this->faker->paragraph(2),
            'status' => $this->faker->randomElement(Status::cases())->value,
            'publication_status' => $this->faker->randomElement(PublicationStatus::cases())->value,
            'publication_date' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'last_review_date' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
            'next_review_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'created_by' => $this->faker->email,
            'updated_by' => $this->faker->optional()->email,
        ];
    }
}

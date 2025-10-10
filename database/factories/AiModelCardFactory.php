<?php

namespace Database\Factories;

use App\Enums\AccessLevel;
use App\Enums\CardFormat;
use App\Enums\CreatorRole;
use App\Enums\Status;
use App\Enums\WorkflowStage;
use App\Models\AiModel;
use App\Models\AiModelVersion;
use App\Enums\TechnicalReviewStatus;
use App\Enums\EthicsReviewStatus;
use App\Enums\ComplianceReviewStatus;
use App\Enums\PublicationStatus;
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
            'ai_model_id' => AiModel::factory(),
            'ai_model_version_id' => AiModelVersion::factory(),
            'title' => $this->faker->sentence,
            'version' => $this->faker->unique()->numerify('v#.##'),
            'creator_role' => CreatorRole::COMMUNITY_CONTRIBUTED,
            'access_level' => AccessLevel::INTERNAL,
            'owner_email' => $this->faker->email,
            'format' => CardFormat::STANDARD,
            'status' => Status::DRAFT,
            'workflow_stage' => WorkflowStage::CREATION,
            'technical_review_status' => TechnicalReviewStatus::PENDING,
            'ethics_review_status' => EthicsReviewStatus::PENDING,
            'compliance_review_status' => ComplianceReviewStatus::PENDING,
            'publication_status' => PublicationStatus::NOT_PUBLISHED,
            'completeness_score' => 80,
            'organizational_context' => $this->faker->paragraph,
            'intended_use' => $this->faker->paragraph,
            'training_data_overview' => $this->faker->paragraph,
            'bias_evaluation_methods' => $this->faker->paragraph,
            'model_limitations' => $this->faker->paragraph,
            'ethical_considerations' => $this->faker->paragraph,
            'risk_summary' => $this->faker->paragraph,
            'performance_summary' => $this->faker->paragraph,
            'latest_performance_date' => now(),
            'publication_date' => now(),
            'last_review_date' => now(),
            'next_review_date' => now()->addYear(),
        ];
    }
}

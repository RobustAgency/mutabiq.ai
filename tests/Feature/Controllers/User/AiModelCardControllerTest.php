<?php

namespace Tests\Feature\Controllers\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\AiModel;
use App\Models\AiModelVersion;
use App\Models\AiModelCard;
use App\Enums\AccessLevel;
use App\Enums\CardFormat;
use App\Enums\Status;
use App\Enums\WorkflowStage;
use App\Enums\TechnicalReviewStatus;
use App\Enums\EthicsReviewStatus;
use App\Enums\ComplianceReviewStatus;
use App\Enums\CreatorRole;
use App\Enums\PublicationStatus;
use App\Models\User;
use Tests\TestCase;

class AiModelCardControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_create_an_ai_model_card(): void
    {
        $user = User::factory()->create();
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'title' => $this->faker->sentence,
            'version' => '1.0.0',
            'creator_role' => CreatorRole::COMMUNITY_CONTRIBUTED,
            'owner_email' => $this->faker->email,
            'access_level' => AccessLevel::INTERNAL,
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
        ];

        $this->actingAs($user);

        $response = $this->postJson('/api/ai-model-cards', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'AI Model Card created successfully',
            ]);

        $this->assertDatabaseHas('ai_model_cards', [
            'title' => $data['title'],
            'version' => $data['version'],
            'owner_email' => $data['owner_email'],
        ]);
    }

    public function test_user_can_update_an_ai_model_card(): void
    {
        $user = User::factory()->create();
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $aiModelCard = AiModelCard::factory()->create([
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'version' => '2.0.0',
            'completeness_score' => 90,
            'status' => Status::DRAFT,
        ];

        $this->actingAs($user);

        $response = $this->postJson("/api/ai-model-cards/{$aiModelCard->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI Model Card updated successfully',
            ]);

        $this->assertDatabaseHas('ai_model_cards', [
            'id' => $aiModelCard->id,
            'title' => 'Updated Title',
            'version' => '2.0.0',
            'completeness_score' => 90,
            'status' => Status::DRAFT,
        ]);
    }
}

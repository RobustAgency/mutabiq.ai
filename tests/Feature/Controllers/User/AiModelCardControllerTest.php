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

    public function test_user_can_list_ai_model_cards(): void
    {
        $user = User::factory()->create();
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        AiModelCard::factory()->count(3)->create([
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
        ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/ai-model-cards");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI Model Cards retrieved successfully',
            ])
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);

        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_user_can_list_ai_model_cards_with_custom_per_page(): void
    {
        $user = User::factory()->create();
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        AiModelCard::factory()->count(10)->create([
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
        ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/ai-model-cards?per_page=5");

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
        $this->assertEquals(10, $response->json('data.total'));
    }

    public function test_user_can_view_single_ai_model_card(): void
    {
        $user = User::factory()->create();
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $aiModelCard = AiModelCard::factory()->create([
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
        ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/ai-model-cards/{$aiModelCard->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI Model Card retrieved successfully',
            ])
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'id',
                    'ai_model_id',
                    'ai_model_version_id',
                    'title',
                    'version',
                    'creator_role',
                    'access_level',
                    'owner_email',
                    'format',
                    'status',
                    'workflow_stage',
                    'ai_model',
                    'ai_model_version',
                ],
            ]);

        $this->assertEquals($aiModelCard->id, $response->json('data.id'));
        $this->assertEquals($aiModelCard->title, $response->json('data.title'));
    }

    public function test_show_returns_ai_model_card_with_relationships(): void
    {
        $user = User::factory()->create();
        $aiModel = AiModel::factory()->create(['name' => 'Test AI Model']);
        $aiModelVersion = AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'version_number' => '1.0.0',
        ]);
        $aiModelCard = AiModelCard::factory()->create([
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
        ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/ai-model-cards/{$aiModelCard->id}");
        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.ai_model'));
        $this->assertNotNull($response->json('data.ai_model_version'));
        $this->assertEquals('Test AI Model', $response->json('data.ai_model.name'));
        $this->assertEquals("1.0.0", $response->json('data.ai_model_version.version'));
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/ai-model-cards');

        $response->assertStatus(401);
    }

    public function test_show_requires_authentication(): void
    {
        $aiModelCard = AiModelCard::factory()->create();

        $response = $this->getJson("/api/ai-model-cards/{$aiModelCard->id}");

        $response->assertStatus(401);
    }

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

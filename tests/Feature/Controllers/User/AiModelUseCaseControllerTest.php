<?php

namespace Tests\Feature\Controller\User;

use App\Enums\AiModelUseCase\RelationshipType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\AiModel;
use App\Models\UseCase;
use App\Models\AiModelVersion;
use App\Models\User;
use App\Models\AiModelUseCase;

class AiModelUseCaseControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private AiModel $aiModel;
    private UseCase $useCase;
    private AiModelVersion $aiModelVersion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->aiModel = AiModel::factory()->create([
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
        $this->useCase = UseCase::factory()->create();
        $this->aiModelVersion = AiModelVersion::factory()->create([
            'ai_model_id' => $this->aiModel->id,
        ]);

        $this->actingAs($this->user);
    }

    private function createAiModelUseCase(array $attributes = []): AiModelUseCase
    {
        return AiModelUseCase::factory()->create(array_merge([
            'ai_model_id' => $this->aiModel->id,
            'use_case_id' => $this->useCase->id,
            'ai_model_version_id' => $this->aiModelVersion->id,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ], $attributes));
    }

    private function getValidAssociationData(array $overrides = []): array
    {
        return array_merge([
            'ai_model_id' => $this->aiModel->id,
            'use_case_id' => $this->useCase->id,
            'ai_model_version_id' => $this->aiModelVersion->id,
            'relationship_type' => RelationshipType::PRIMARY,
        ], $overrides);
    }

    public function test_user_can_list_ai_model_use_case_associations(): void
    {
        AiModelUseCase::factory()->count(3)->create([
            'ai_model_id' => $this->aiModel->id,
        ]);

        $response = $this->getJson('/api/ai-model-use-cases?ai_model_id=' . $this->aiModel->id);

        $response->assertOk()
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'ai_model_id',
                            'use_case_id',
                            'ai_model_version_id',
                            'relationship_type',
                            'created_by',
                            'updated_by',
                            'created_at',
                            'updated_at',
                            'ai_model',
                            'use_case',
                            'ai_model_version',
                        ],
                    ],
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total',
                ],
            ]);
    }

    public function test_user_can_create_ai_model_use_case_association(): void
    {
        $data = $this->getValidAssociationData();

        $response = $this->postJson('/api/ai-model-use-cases', $data);

        $response->assertCreated()
            ->assertJson([
                'error' => false,
                'message' => 'AI Model Use Case association created successfully',
                'data' => null,
            ]);

        $this->assertDatabaseHas('ai_model_use_cases', $data);
        $this->assertDatabaseHas('ai_model_use_cases', [
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);
    }

    public function test_user_can_retrieve_ai_model_use_case_association(): void
    {
        $aiModelUseCase = $this->createAiModelUseCase();

        $response = $this->getJson("/api/ai-model-use-cases/{$aiModelUseCase->id}");

        $response->assertOk()
            ->assertJson([
                'error' => false,
                'message' => 'AI Model Use Case association retrieved successfully',
                'data' => [
                    'id' => $aiModelUseCase->id,
                    'relationship_type' => $aiModelUseCase->relationship_type,
                    'ai_model' => [
                        'id' => $this->aiModel->id,
                        'name' => $this->aiModel->name,
                    ],
                    'use_case' => [
                        'id' => $this->useCase->id,
                        'title' => $this->useCase->title,
                    ],
                    'ai_model_version' => [
                        'id' => $this->aiModelVersion->id,
                    ],
                    'created_by' => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                    ],
                    'updated_by' => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                    ],
                ],
            ]);
    }

    public function test_user_can_update_ai_model_use_case_association(): void
    {
        $aiModelUseCase = $this->createAiModelUseCase(['relationship_type' => RelationshipType::PRIMARY]);

        $updateData = [
            'relationship_type' => RelationshipType::SECONDARY,
            'updated_by' => $this->user->id,
        ];

        $response = $this->postJson("/api/ai-model-use-cases/{$aiModelUseCase->id}", $updateData);

        $response->assertOk()
            ->assertJson([
                'error' => false,
                'message' => 'AI Model Use Case association updated successfully',
                'data' => null,
            ]);

        $this->assertDatabaseHas('ai_model_use_cases', array_merge(['id' => $aiModelUseCase->id], $updateData));
        $this->assertDatabaseHas('ai_model_use_cases', [
            'updated_by' => $this->user->id,
        ]);
    }

    public function test_user_can_delete_ai_model_use_case_association(): void
    {
        $aiModelUseCase = $this->createAiModelUseCase();

        $response = $this->deleteJson("/api/ai-model-use-cases/{$aiModelUseCase->id}");

        $response->assertOk()
            ->assertJson([
                'error' => false,
                'message' => 'AI Model Use Case association deleted successfully',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('ai_model_use_cases', ['id' => $aiModelUseCase->id]);
    }

    public function test_it_validates_relationship_type_on_create(): void
    {
        $data = $this->getValidAssociationData(['relationship_type' => 'invalid_type']);

        $response = $this->postJson('/api/ai-model-use-cases', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['relationship_type']);
    }
}

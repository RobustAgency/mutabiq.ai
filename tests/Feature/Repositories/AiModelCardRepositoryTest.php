<?php

namespace Tests\Feature\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Repositories\AiModelCardRepository;
use App\Enums\AccessLevel;
use App\Enums\CardFormat;
use App\Enums\Status;
use App\Enums\WorkflowStage;
use App\Enums\TechnicalReviewStatus;
use App\Enums\EthicsReviewStatus;
use App\Enums\ComplianceReviewStatus;
use App\Enums\CreatorRole;
use App\Enums\PublicationStatus;
use App\Models\AiModel;
use App\Models\AiModelVersion;
use App\Models\AiModelCard;
use App\Models\Organization;
use App\Models\User;
use App\Models\Stakeholder;
use Tests\TestCase;

class AiModelCardRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private AiModelCardRepository $aiModelCardRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiModelCardRepository = app(AiModelCardRepository::class);
    }

    public function test_it_can_get_paginated_ai_model_version_cards(): void
    {
        $organization = Organization::factory()->create();
        $aiModel = AiModel::factory()->create(['organization_id' => $organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        AiModelCard::factory()->count(15)->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
        ]);

        $result = $this->aiModelCardRepository->getPaginatedAiModelCardsByOrganizationID($organization->id, 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
        $this->assertEquals(2, $result->lastPage());
    }

    public function test_it_can_get_paginated_ai_model_cards_with_custom_per_page(): void
    {
        $organization = Organization::factory()->create();
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        AiModelCard::factory()->count(20)->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
        ]);

        $result = $this->aiModelCardRepository->getPaginatedAiModelCardsByOrganizationID($organization->id, 5);

        $this->assertCount(5, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(4, $result->lastPage());
    }

    public function test_it_create_an_ai_model_card(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $stakeholder = Stakeholder::factory()->create(['organization_id' => $organization->id]);
        $aiModel = AiModel::factory()->create(['organization_id' => $organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $data = [
            'organization_id' => $organization->id,
            'owner_stakeholder_id' => $stakeholder->id,
            'version_id' => $aiModelVersion->id,
            'model_overview' => $this->faker->paragraph,
            'title' => $this->faker->sentence,
            'version' => '1.0.0',
            'creator_role' => CreatorRole::INTERNAL_TEAM,
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
            'latest_performance_date' => now(),
            'publication_date' => now(),
            'last_review_date' => now(),
            'next_review_date' => now()->addYear(),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ];

        $aiModelCard = $this->aiModelCardRepository->createAiModelCard($data);

        $this->assertInstanceOf(AiModelCard::class, $aiModelCard);
        $this->assertEquals($data['title'], $aiModelCard->title);

        $this->assertDatabaseHas('ai_model_cards', [
            'id' => $aiModelCard->id,
            'title' => $data['title'],
        ]);
    }

    public function test_it_update_an_ai_model_card(): void
    {
        $organization = Organization::factory()->create();
        $aiModelCard = AiModelCard::factory()->create([
            'title' => 'Original Title',
            'organization_id' => $organization->id,
        ]);

        $data = [
            'title' => 'Updated Title',
        ];

        $updatedAiModelCard = $this->aiModelCardRepository->updateAiModelCard($aiModelCard, $data);

        $this->assertTrue($updatedAiModelCard);

        $this->assertDatabaseHas('ai_model_cards', [
            'id' => $aiModelCard->id,
            'title' => 'Updated Title',
        ]);
    }
}

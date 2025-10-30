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

    public function test_it_can_get_paginated_ai_model_cards(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        AiModelCard::factory()->count(15)->create([
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
        ]);

        $result = $this->aiModelCardRepository->getPaginatedAiModelCards($aiModel->id, 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
        $this->assertEquals(2, $result->lastPage());
    }

    public function test_it_can_get_paginated_ai_model_cards_with_custom_per_page(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        AiModelCard::factory()->count(20)->create([
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
        ]);

        $result = $this->aiModelCardRepository->getPaginatedAiModelCards($aiModel->id, 5);

        $this->assertCount(5, $result->items());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(4, $result->lastPage());
    }

    public function test_it_filters_by_ai_model_id(): void
    {
        $aiModel1 = AiModel::factory()->create();
        $aiModel2 = AiModel::factory()->create();
        $aiModelVersion1 = AiModelVersion::factory()->create(['ai_model_id' => $aiModel1->id]);
        $aiModelVersion2 = AiModelVersion::factory()->create(['ai_model_id' => $aiModel2->id]);

        AiModelCard::factory()->count(5)->create([
            'ai_model_id' => $aiModel1->id,
            'ai_model_version_id' => $aiModelVersion1->id,
        ]);

        AiModelCard::factory()->count(3)->create([
            'ai_model_id' => $aiModel2->id,
            'ai_model_version_id' => $aiModelVersion2->id,
        ]);

        $result = $this->aiModelCardRepository->getPaginatedAiModelCards($aiModel1->id, 10);

        $this->assertCount(5, $result->items());
        foreach ($result->items() as $card) {
            $this->assertEquals($aiModel1->id, $card->ai_model_id);
        }
    }

    public function test_it_can_get_ai_model_card_by_id_with_relationships(): void
    {
        $aiModel = AiModel::factory()->create(['name' => 'Test Model']);
        $aiModelVersion = AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
            'version_number' => '1.0.0',
        ]);
        $aiModelCard = AiModelCard::factory()->create([
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
        ]);

        $result = $this->aiModelCardRepository->getAiModelCardById($aiModelCard);

        $this->assertInstanceOf(AiModelCard::class, $result);
        $this->assertTrue($result->relationLoaded('aiModel'));
        $this->assertTrue($result->relationLoaded('aiModelVersion'));
        $this->assertEquals('Test Model', $result->aiModel->name);
        $this->assertEquals('1.0.0', $result->aiModelVersion->version_number);
    }

    public function test_it_create_an_ai_model_card(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
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
        $aiModelCard = AiModelCard::factory()->create([
            'title' => 'Original Title',
            'version' => '1.0.0',
            'creator_role' => 'developer',
            'owner_email' => $this->faker->email,
            'access_level' => AccessLevel::INTERNAL,
            'format' => CardFormat::STANDARD,
            'status' => Status::DRAFT,
            'workflow_stage' => WorkflowStage::CREATION,
            'technical_review_status' => TechnicalReviewStatus::PENDING,
            'ethics_review_status' => EthicsReviewStatus::PENDING,
            'compliance_review_status' => ComplianceReviewStatus::PENDING,
            'publication_status' => PublicationStatus::NOT_PUBLISHED,
        ]);

        $data = [
            'title' => 'Updated Title',
            'version' => '1.1.0',
            'status' => Status::DRAFT,
            'completeness_score' => 90,
        ];

        $updatedAiModelCard = $this->aiModelCardRepository->updateAiModelCard($aiModelCard, $data);

        $this->assertTrue($updatedAiModelCard);

        $this->assertDatabaseHas('ai_model_cards', [
            'id' => $aiModelCard->id,
            'title' => 'Updated Title',
            'version' => '1.1.0',
            'status' => Status::DRAFT,
            'completeness_score' => 90,
        ]);
    }

    public function test_it_can_create_model_card_with_all_enums(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'title' => 'Complete Model Card',
            'version' => '1.0.0',
            'creator_role' => CreatorRole::INTERNAL_TEAM,
            'access_level' => AccessLevel::PUBLIC,
            'format' => CardFormat::REGULATORY,
            'status' => Status::PUBLISHED,
            'workflow_stage' => WorkflowStage::TECHNICAL_REVIEW,
            'technical_review_status' => TechnicalReviewStatus::PASSED,
            'ethics_review_status' => EthicsReviewStatus::APPROVED,
            'compliance_review_status' => ComplianceReviewStatus::COMPLIANT,
            'publication_status' => PublicationStatus::PUBLISHED_INTERNAL,
            'owner_email' => $this->faker->email,
        ];

        $aiModelCard = $this->aiModelCardRepository->createAiModelCard($data);

        $this->assertEquals(CreatorRole::INTERNAL_TEAM, $aiModelCard->creator_role);
        $this->assertEquals(AccessLevel::PUBLIC, $aiModelCard->access_level);
        $this->assertEquals(CardFormat::REGULATORY, $aiModelCard->format);
        $this->assertEquals(Status::PUBLISHED, $aiModelCard->status);
        $this->assertEquals(WorkflowStage::TECHNICAL_REVIEW, $aiModelCard->workflow_stage);
        $this->assertEquals(TechnicalReviewStatus::PASSED, $aiModelCard->technical_review_status);
        $this->assertEquals(EthicsReviewStatus::APPROVED, $aiModelCard->ethics_review_status);
        $this->assertEquals(ComplianceReviewStatus::COMPLIANT, $aiModelCard->compliance_review_status);
        $this->assertEquals(PublicationStatus::PUBLISHED_INTERNAL, $aiModelCard->publication_status);
    }

    public function test_it_can_update_completeness_score(): void
    {
        $aiModelCard = AiModelCard::factory()->create(['completeness_score' => 50]);

        $this->aiModelCardRepository->updateAiModelCard($aiModelCard, [
            'completeness_score' => 100,
        ]);

        $aiModelCard->refresh();
        $this->assertEquals(100, $aiModelCard->completeness_score);
    }

    public function test_it_returns_empty_paginated_result_for_non_existent_model(): void
    {
        $result = $this->aiModelCardRepository->getPaginatedAiModelCards(99999, 10);

        $this->assertCount(0, $result->items());
        $this->assertEquals(0, $result->total());
    }
}

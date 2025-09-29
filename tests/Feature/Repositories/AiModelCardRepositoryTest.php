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

    public function test_it_create_an_ai_model_card(): void
    {
        $aiModel = AiModel::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $data = [
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'title' => $this->faker->sentence,
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
}

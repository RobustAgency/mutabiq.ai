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

        $result = $this->aiModelCardRepository->getFilteredAiModelCards(['organization_id' => $organization->id, 'per_page' => 10]);

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

        $result = $this->aiModelCardRepository->getFilteredAiModelCards(['organization_id' => $organization->id, 'per_page' => 5]);

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

    public function test_it_filters_by_creator_role(): void
    {
        $organization = Organization::factory()->create();
        $aiModel = AiModel::factory()->create(['organization_id' => $organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'creator_role' => CreatorRole::INTERNAL_TEAM,
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'creator_role' => CreatorRole::COMMUNITY_CONTRIBUTED,
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'creator_role' => CreatorRole::INTERNAL_TEAM,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'creator_role' => CreatorRole::INTERNAL_TEAM,
        ];
        $result = $this->aiModelCardRepository->getFilteredAiModelCards($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_it_filters_by_status(): void
    {
        $organization = Organization::factory()->create();
        $aiModel = AiModel::factory()->create(['organization_id' => $organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'status' => Status::DRAFT,
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'status' => Status::PUBLISHED,
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'status' => Status::DRAFT,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'status' => Status::DRAFT,
        ];
        $result = $this->aiModelCardRepository->getFilteredAiModelCards($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_it_filters_by_format(): void
    {
        $organization = Organization::factory()->create();
        $aiModel = AiModel::factory()->create(['organization_id' => $organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'format' => CardFormat::STANDARD,
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'format' => CardFormat::REGULATORY,
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'format' => CardFormat::STANDARD,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'format' => CardFormat::STANDARD,
        ];
        $result = $this->aiModelCardRepository->getFilteredAiModelCards($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_it_filters_by_publication_status(): void
    {
        $organization = Organization::factory()->create();
        $aiModel = AiModel::factory()->create(['organization_id' => $organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'publication_status' => PublicationStatus::PUBLISHED_INTERNAL,
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'publication_status' => PublicationStatus::NOT_PUBLISHED,
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'publication_status' => PublicationStatus::PUBLISHED_INTERNAL,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'publication_status' => PublicationStatus::PUBLISHED_INTERNAL,
        ];
        $result = $this->aiModelCardRepository->getFilteredAiModelCards($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_it_filters_by_owner(): void
    {
        $organization = Organization::factory()->create();
        $aiModel = AiModel::factory()->create(['organization_id' => $organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        $stakeholder1 = Stakeholder::factory()->create([
            'organization_id' => $organization->id,
            'display_name' => 'John Smith',
        ]);
        $stakeholder2 = Stakeholder::factory()->create([
            'organization_id' => $organization->id,
            'display_name' => 'Jane Doe',
        ]);

        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'owner_stakeholder_id' => $stakeholder1->id,
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'owner_stakeholder_id' => $stakeholder2->id,
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'owner_stakeholder_id' => $stakeholder1->id,
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'owner' => 'John',
        ];
        $result = $this->aiModelCardRepository->getFilteredAiModelCards($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_it_filters_by_date_range(): void
    {
        $organization = Organization::factory()->create();
        $aiModel = AiModel::factory()->create(['organization_id' => $organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(10),
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(5),
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(1),
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'from' => now()->subDays(7)->format('Y-m-d'),
            'to' => now()->subDays(2)->format('Y-m-d'),
        ];
        $result = $this->aiModelCardRepository->getFilteredAiModelCards($filters);

        $this->assertCount(1, $result->items());
    }

    public function test_it_filters_by_from_date_only(): void
    {
        $organization = Organization::factory()->create();
        $aiModel = AiModel::factory()->create(['organization_id' => $organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(10),
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(5),
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(1),
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'from' => now()->subDays(6)->format('Y-m-d'),
        ];
        $result = $this->aiModelCardRepository->getFilteredAiModelCards($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_it_filters_by_to_date_only(): void
    {
        $organization = Organization::factory()->create();
        $aiModel = AiModel::factory()->create(['organization_id' => $organization->id]);
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);

        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(10),
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(5),
        ]);
        AiModelCard::factory()->create([
            'version_id' => $aiModelVersion->id,
            'organization_id' => $organization->id,
            'created_at' => now()->subDays(1),
        ]);

        $filters = [
            'organization_id' => $organization->id,
            'to' => now()->subDays(6)->format('Y-m-d'),
        ];
        $result = $this->aiModelCardRepository->getFilteredAiModelCards($filters);

        $this->assertCount(1, $result->items());
    }
}

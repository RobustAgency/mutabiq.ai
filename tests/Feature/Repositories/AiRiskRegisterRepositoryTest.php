<?php

namespace Tests\Feature\Repositories;

use App\Enums\AiRiskRegister\ReviewCadence;
use App\Enums\AiRiskRegister\RiskCategory;
use App\Enums\AiRiskRegister\RiskDecision;
use App\Enums\AiRiskRegister\RiskLevel;
use App\Enums\AiRiskRegister\RiskStatus;
use App\Models\AiIncident;
use App\Models\AiModel;
use App\Models\AiModelVersion;
use App\Models\AiRiskRegister;
use App\Models\CorrectivePreventiveAction;
use App\Models\Organization;
use App\Models\Stakeholder;
use App\Models\UseCase;
use App\Models\User;
use App\Repositories\AiRiskRegisterRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiRiskRegisterRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AiRiskRegisterRepository $repository;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AiRiskRegisterRepository();
        $this->organization = Organization::factory()->create();
    }

    public function test_get_paginated_returns_paginated_ai_risk_register_entries(): void
    {
        AiRiskRegister::factory()->count(20)->create([
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->repository->getPaginatedAiRiskRegister($this->organization->id, 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }

    public function test_get_paginated_with_default_per_page(): void
    {
        AiRiskRegister::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->repository->getPaginatedAiRiskRegister($this->organization->id);

        $this->assertCount(5, $result->items());
    }

    public function test_get_paginated_loads_relationships(): void
    {
        AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->repository->getPaginatedAiRiskRegister($this->organization->id);

        $firstItem = $result->items()[0];
        $this->assertTrue($firstItem->relationLoaded('aiModel'));
        $this->assertTrue($firstItem->relationLoaded('riskOwner'));
    }

    public function test_get_paginated_filters_by_organization(): void
    {
        $otherOrganization = Organization::factory()->create();

        AiRiskRegister::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
        ]);
        AiRiskRegister::factory()->count(3)->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $result = $this->repository->getPaginatedAiRiskRegister($this->organization->id);

        $this->assertEquals(5, $result->total());
    }

    public function test_create_stores_ai_risk_register_with_required_fields(): void
    {
        $aiModel = AiModel::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $stakeholder = Stakeholder::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'Data Privacy Risk',
            'risk_category' => RiskCategory::PRIVACY->value,
            'ai_model_id' => $aiModel->id,
            'description' => 'Risk of unauthorized data access',
            'method_id' => 1,
            'likelihood_code' => 'L3',
            'impact_code' => 'I4',
            'risk_level' => RiskLevel::HIGH->value,
            'decision' => RiskDecision::TREAT->value,
            'risk_owner' => $stakeholder->id,
            'review_cadence' => ReviewCadence::QUARTERLY->value,
            'next_review_due' => now()->addMonths(3)->toDateString(),
            'status' => RiskStatus::ASSESSED->value,
            'created_by' => $user->id,
        ];

        $aiRiskRegister = $this->repository->createAiRiskRegister($data);

        $this->assertInstanceOf(AiRiskRegister::class, $aiRiskRegister);
        $this->assertEquals('Data Privacy Risk', $aiRiskRegister->title);
        $this->assertEquals(RiskCategory::PRIVACY->value, $aiRiskRegister->risk_category);
        $this->assertEquals(RiskLevel::HIGH->value, $aiRiskRegister->risk_level);

        $this->assertDatabaseHas('ai_risk_register', [
            'title' => 'Data Privacy Risk',
            'risk_category' => RiskCategory::PRIVACY->value,
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_create_stores_ai_risk_register_with_all_fields(): void
    {
        $aiModel = AiModel::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $aiModelVersion = AiModelVersion::factory()->create([
            'ai_model_id' => $aiModel->id,
        ]);
        $useCase = UseCase::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $stakeholder = Stakeholder::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $incident = AiIncident::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $capa = CorrectivePreventiveAction::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'organization_id' => $this->organization->id,
            'title' => 'Comprehensive Risk',
            'risk_category' => RiskCategory::SECURITY->value,
            'ai_model_id' => $aiModel->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'use_case_id' => $useCase->id,
            'description' => 'Security vulnerability in model deployment',
            'related_controls' => ['CTRL-001', 'CTRL-002'],
            'method_id' => 1,
            'likelihood_code' => 'L4',
            'impact_code' => 'I5',
            'inherent_score' => '9',
            'residual_score' => '6',
            'risk_level' => RiskLevel::CRITICAL->value,
            'decision' => RiskDecision::TREAT->value,
            'risk_owner' => $stakeholder->id,
            'review_cadence' => ReviewCadence::MONTHLY->value,
            'next_review_due' => now()->addMonth()->toDateString(),
            'status' => RiskStatus::IN_TREATMENT->value,
            'linked_assessment_id' => 123,
            'linked_incident_id' => $incident->id,
            'linked_capa_id' => $capa->id,
            'evidence_link' => 'https://example.com/evidence',
            'likelihood_label_snapshot' => 'Likely',
            'impact_label_snapshot' => 'Catastrophic',
            'method_name_snapshot' => '5x5 Matrix',
            'created_by' => $user->id,
        ];

        $aiRiskRegister = $this->repository->createAiRiskRegister($data);

        $this->assertEquals('Comprehensive Risk', $aiRiskRegister->title);
        $this->assertEquals(RiskCategory::SECURITY->value, $aiRiskRegister->risk_category);
        $this->assertEquals($aiModelVersion->id, $aiRiskRegister->ai_model_version_id);
        $this->assertEquals($useCase->id, $aiRiskRegister->use_case_id);
        $this->assertEquals(['CTRL-001', 'CTRL-002'], $aiRiskRegister->related_controls);
        $this->assertEquals($incident->id, $aiRiskRegister->linked_incident_id);
        $this->assertEquals($capa->id, $aiRiskRegister->linked_capa_id);
    }

    public function test_update_modifies_ai_risk_register(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Original Title',
            'risk_level' => RiskLevel::MEDIUM->value,
        ]);

        $updatedData = [
            'title' => 'Updated Title',
            'risk_level' => RiskLevel::HIGH->value,
        ];

        $result = $this->repository->updateAiRiskRegister($aiRiskRegister, $updatedData);

        $this->assertEquals('Updated Title', $result->title);
        $this->assertEquals(RiskLevel::HIGH->value, $result->risk_level);

        $this->assertDatabaseHas('ai_risk_register', [
            'id' => $aiRiskRegister->id,
            'title' => 'Updated Title',
            'risk_level' => RiskLevel::HIGH->value,
        ]);
    }

    public function test_update_returns_fresh_instance(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => RiskStatus::IDENTIFIED->value,
        ]);

        $result = $this->repository->updateAiRiskRegister($aiRiskRegister, [
            'status' => RiskStatus::ASSESSED->value,
        ]);

        $this->assertNotSame($aiRiskRegister, $result);
        $this->assertEquals(RiskStatus::ASSESSED->value, $result->status);
    }

    public function test_delete_removes_ai_risk_register(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->repository->deleteAiRiskRegister($aiRiskRegister);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('ai_risk_register', [
            'id' => $aiRiskRegister->id,
        ]);
    }

    public function test_get_by_id_loads_all_relationships(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->repository->getAiRiskRegisterByID($aiRiskRegister);

        $this->assertTrue($result->relationLoaded('aiModel'));
        $this->assertTrue($result->relationLoaded('aiModelVersion'));
        $this->assertTrue($result->relationLoaded('useCase'));
        $this->assertTrue($result->relationLoaded('riskOwner'));
    }
}

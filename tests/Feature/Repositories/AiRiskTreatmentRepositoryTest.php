<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Models\AiRiskRegister;
use App\Models\AiRiskTreatment;
use Illuminate\Foundation\Testing\WithFaker;
use App\Repositories\AiRiskTreatmentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiRiskTreatmentRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private AiRiskTreatmentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(AiRiskTreatmentRepository::class);
    }

    public function test_get_filtered_ai_risk_treatments_by_organization_and_filters(): void
    {
        $org = Organization::factory()->create();
        $otherOrg = Organization::factory()->create();

        AiRiskTreatment::factory()->count(3)->create(['organization_id' => $org->id, 'status' => 'open', 'treatment_type' => 'mitigation']);
        AiRiskTreatment::factory()->count(2)->create(['organization_id' => $otherOrg->id, 'status' => 'open', 'treatment_type' => 'mitigation']);
        AiRiskTreatment::factory()->create(['organization_id' => $org->id, 'status' => 'completed', 'treatment_type' => 'remediation']);

        $result = $this->repository->getFilteredAiRiskTreatments([
            'organization_id' => $org->id,
            'status' => 'open',
            'treatment_type' => 'mitigation',
            'per_page' => 15,
        ]);

        $this->assertEquals(3, $result->total());
        $this->assertTrue(collect($result->items())->every(fn ($item) => $item->organization_id === $org->id && $item->status === 'open'));
    }

    public function test_create_ai_risk_treatment(): void
    {
        $org = Organization::factory()->create();
        $stakeholder = Stakeholder::factory()->create(['organization_id' => $org->id]);
        $aiRiskRegister = AiRiskRegister::factory()->create(['organization_id' => $org->id]);

        $data = [
            'organization_id' => $org->id,
            'ai_risk_register_id' => $aiRiskRegister->id,
            'treatment_type' => 'mitigation',
            'plan_summary' => 'Test mitigation plan',
            'owner_stakeholder_id' => $stakeholder->id,
            'assignee' => [['id' => $stakeholder->id, 'name' => $stakeholder->name]],
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'open',
        ];

        $treatment = $this->repository->createAiRiskTreatment($data);

        $this->assertInstanceOf(AiRiskTreatment::class, $treatment);
        $this->assertDatabaseHas('ai_risk_treatments', ['id' => $treatment->id, 'organization_id' => $org->id, 'status' => 'open']);
    }

    public function test_update_ai_risk_treatment(): void
    {
        $treatment = AiRiskTreatment::factory()->create(['status' => 'open']);

        $result = $this->repository->updateAiRiskTreatment($treatment, ['status' => 'in_progress']);

        $this->assertTrue($result);
        $this->assertDatabaseHas('ai_risk_treatments', ['id' => $treatment->id, 'status' => 'in_progress']);
    }

    public function test_delete_ai_risk_treatment(): void
    {
        $treatment = AiRiskTreatment::factory()->create();

        $result = $this->repository->deleteAiRiskTreatment($treatment);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('ai_risk_treatments', ['id' => $treatment->id]);
    }

    public function test_get_filtered_ai_risk_treatments_pagination(): void
    {
        $org = Organization::factory()->create();

        AiRiskTreatment::factory(22)->create(['organization_id' => $org->id]);

        $result = $this->repository->getFilteredAiRiskTreatments([
            'organization_id' => $org->id,
            'per_page' => 10,
        ]);

        $this->assertEquals(22, $result->total());
        $this->assertCount(10, $result->items());
        $this->assertTrue($result->hasPages());
    }
}

<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\Organization;
use App\Models\RiskMethodology;
use Illuminate\Foundation\Testing\WithFaker;
use App\Repositories\RiskMethodologyRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RiskMethodologyRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private RiskMethodologyRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RiskMethodologyRepository;
    }

    public function test_get_filtered_risk_methodologies_by_organization(): void
    {
        $organization = Organization::factory()->create();
        $otherOrganization = Organization::factory()->create();

        RiskMethodology::factory(3)->create(['organization_id' => $organization->id]);
        RiskMethodology::factory(2)->create(['organization_id' => $otherOrganization->id]);

        $result = $this->repository->getFilteredRiskMethodologies([
            'organization_id' => $organization->id,
            'per_page' => 15,
        ]);

        $this->assertEquals(3, $result->total());
        $this->assertTrue(collect($result->items())->every(fn ($item) => $item->organization_id === $organization->id));
    }

    public function test_get_filtered_risk_methodologies_by_name(): void
    {
        $organization = Organization::factory()->create();

        RiskMethodology::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Risk Assessment Framework',
        ]);
        RiskMethodology::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Compliance Policy',
        ]);

        $result = $this->repository->getFilteredRiskMethodologies([
            'organization_id' => $organization->id,
            'name' => 'Risk',
            'per_page' => 15,
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertStringContainsString('Risk', $result->items()[0]->name);
    }

    public function test_get_filtered_risk_methodologies_by_effective_from(): void
    {
        $organization = Organization::factory()->create();

        RiskMethodology::factory()->create([
            'organization_id' => $organization->id,
            'effective_from' => '2025-01-01',
        ]);
        RiskMethodology::factory()->create([
            'organization_id' => $organization->id,
            'effective_from' => '2025-06-01',
        ]);

        $result = $this->repository->getFilteredRiskMethodologies([
            'organization_id' => $organization->id,
            'effective_from' => '2025-05-01',
            'per_page' => 15,
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertTrue($result->items()[0]->effective_from >= '2025-05-01');
    }

    public function test_get_filtered_risk_methodologies_by_effective_to(): void
    {
        $organization = Organization::factory()->create();

        RiskMethodology::factory()->create([
            'organization_id' => $organization->id,
            'effective_to' => '2025-12-31',
        ]);
        RiskMethodology::factory()->create([
            'organization_id' => $organization->id,
            'effective_to' => '2026-12-31',
        ]);

        $result = $this->repository->getFilteredRiskMethodologies([
            'organization_id' => $organization->id,
            'effective_to' => '2026-01-01',
            'per_page' => 15,
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertTrue($result->items()[0]->effective_to <= '2026-01-01');
    }

    public function test_create_risk_methodology(): void
    {
        $organization = Organization::factory()->create();
        $data = [
            'organization_id' => $organization->id,
            'name' => 'New Risk Methodology',
            'likelihood_scale' => ['rare', 'possible', 'likely'],
            'impact_scale' => ['minor', 'moderate', 'major'],
            'matrix_rule' => [
                'rare' => ['minor' => 'low', 'moderate' => 'low', 'major' => 'medium'],
                'possible' => ['minor' => 'low', 'moderate' => 'medium', 'major' => 'high'],
                'likely' => ['minor' => 'medium', 'moderate' => 'high', 'major' => 'high'],
            ],
            'aggregation_logic' => 'mean',
            'review_policy' => 'Annual review',
            'owner_team' => 'Risk Management',
            'effective_from' => '2025-01-01',
            'effective_to' => '2026-01-01',
            'source_created_at' => now(),
            'acceptance_thresholds' => 'hola',
        ];

        $methodology = $this->repository->createRiskMethodology($data);

        $this->assertInstanceOf(RiskMethodology::class, $methodology);
        $this->assertEquals($data['name'], $methodology->name);
        $this->assertDatabaseHas('risk_methodologies', ['id' => $methodology->id]);
    }

    public function test_update_risk_methodology(): void
    {
        $methodology = RiskMethodology::factory()->create();
        $newName = 'Updated Methodology';

        $updated = $this->repository->updateRiskMethodology($methodology, [
            'name' => $newName,
        ]);

        $this->assertEquals($newName, $updated->name);
        $this->assertDatabaseHas('risk_methodologies', [
            'id' => $methodology->id,
            'name' => $newName,
        ]);
    }

    public function test_delete_risk_methodology(): void
    {
        $methodology = RiskMethodology::factory()->create();

        $result = $this->repository->deleteRiskMethodology($methodology);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('risk_methodologies', ['id' => $methodology->id]);
    }

    public function test_get_risk_methodology(): void
    {
        $methodology = RiskMethodology::factory()->create();

        $retrieved = $this->repository->getRiskMethodology($methodology);

        $this->assertInstanceOf(RiskMethodology::class, $retrieved);
        $this->assertEquals($methodology->id, $retrieved->id);
        $this->assertNotNull($retrieved->organization);
    }

    public function test_get_filtered_risk_methodologies_pagination(): void
    {
        $organization = Organization::factory()->create();

        RiskMethodology::factory(20)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getFilteredRiskMethodologies([
            'organization_id' => $organization->id,
            'per_page' => 10,
        ]);

        $this->assertEquals(10, count($result->items()));
        $this->assertEquals(20, $result->total());
        $this->assertTrue($result->hasPages());
    }
}

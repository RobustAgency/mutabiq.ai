<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\Framework;
use App\Models\Requirement;
use App\Repositories\RequirementRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequirementRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private RequirementRepository $requirementRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requirementRepository = app(RequirementRepository::class);
    }

    public function test_it_filter_requirements_by_category(): void
    {

        Requirement::factory()->create([
            'category' => 'Security',
        ]);

        Requirement::factory()->create([
            'category' => 'Privacy',
        ]);

        $results = $this->requirementRepository->getFilteredRequirements(['category' => 'Security']);

        $this->assertCount(1, $results);
        $this->assertEquals('Security', $results->first()->category);
    }

    public function test_it_filter_requirements_by_priority(): void
    {

        Requirement::factory()->create([
            'priority' => 'High',
        ]);

        Requirement::factory()->create([
            'priority' => 'Low',
        ]);

        $results = $this->requirementRepository->getFilteredRequirements(['priority' => 'High']);

        $this->assertCount(1, $results);
        $this->assertEquals('High', $results->first()->priority);
    }

    public function test_it_applies_pagination_correctly(): void
    {

        Requirement::factory()->count(15)->create();

        $results = $this->requirementRepository->getFilteredRequirements(['per_page' => 5]);

        $this->assertCount(5, $results);
        $this->assertEquals(5, $results->perPage());
        $this->assertEquals(15, $results->total());
    }

    public function test_it_creates_requirement(): void
    {
        $framework = Framework::factory()->create();

        $data = [
            'reference' => 'REQ-001',
            'requirement_text' => 'System must be secure',
            'category' => 'security',
            'applicability' => 'All AI Systems',
            'effective_from' => now(),
            'effective_to' => now()->addYear(),
            'priority' => 'high',
            'tags' => json_encode(['compliance', 'mandatory']),
            'framework_id' => $framework->id,
        ];

        $requirement = $this->requirementRepository->createForAdmin($data);
        $this->assertNotNull($requirement->id);
        $this->assertEquals('REQ-001', $requirement->reference);
        $this->assertEquals('System must be secure', $requirement->requirement_text);
        $this->assertEquals('security', $requirement->category);
        $this->assertDatabaseHas('requirements', [
            'id' => $requirement->id,
            'reference' => 'REQ-001',
        ]);
    }

    public function test_it_updates_requirement(): void
    {
        $framework1 = Framework::factory()->create();
        $framework2 = Framework::factory()->create();

        $requirement = Requirement::factory()->create([
            'reference' => 'REQ-001',
            'priority' => 'Low',
            'framework_id' => $framework1->id,
        ]);

        $data = [
            'reference' => 'REQ-001-UPDATED',
            'priority' => 'High',
            'framework_id' => $framework2->id,
        ];

        $updatedRequirement = $this->requirementRepository->update($requirement, $data);

        $this->assertEquals('REQ-001-UPDATED', $updatedRequirement->reference);
        $this->assertEquals('High', $updatedRequirement->priority);

    }
}

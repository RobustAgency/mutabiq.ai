<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\Framework;
use App\Repositories\FrameworkRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FrameworkRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_it_filter_frameworks_by_name(): void
    {
        $user = User::factory()->create();

        Framework::factory()->create([
            'user_id' => $user->id,
            'name' => 'EU AI Act',
        ]);

        Framework::factory()->create([
            'user_id' => $user->id,
            'name' => 'ISO 42001',
        ]);

        $repository = app(FrameworkRepository::class);
        $results = $repository->getFilteredFrameworks($user, ['name' => 'AI']);

        $this->assertCount(1, $results);
        $this->assertEquals('EU AI Act', $results->first()->name);
    }

    public function test_it_filter_frameworks_by_status(): void
    {
        $user = User::factory()->create();

        Framework::factory()->create([
            'user_id' => $user->id,
            'name' => 'Published Framework',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'user_id' => $user->id,
            'name' => 'Draft Framework',
            'is_published' => false,
        ]);

        $repository = app(FrameworkRepository::class);
        $results = $repository->getFilteredFrameworks($user, ['status' => true]);

        $this->assertCount(1, $results);
        $this->assertEquals('Published Framework', $results->first()->name);
    }

    public function test_it_applies_pagination_correctly(): void
    {
        $user = User::factory()->create();

        Framework::factory()->count(15)->create([
            'user_id' => $user->id,
        ]);

        $repository = app(FrameworkRepository::class);
        $results = $repository->getFilteredFrameworks($user, ['per_page' => 5]);

        $this->assertCount(5, $results);
        $this->assertEquals(5, $results->perPage());
        $this->assertEquals(15, $results->total());
    }

    public function test_it_get_published_frameworks(): void
    {
        Framework::factory()->create([
            'name' => 'Published Framework 1',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Published Framework 2',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Draft Framework',
            'is_published' => false,
        ]);

        $repository = app(FrameworkRepository::class);
        $results = $repository->getPublishedFrameworks();

        $this->assertCount(2, $results);
    }

    public function test_it_get_framework_by_id(): void
    {
        $user = User::factory()->create();

        $framework = Framework::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Framework',
        ]);

        $repository = app(FrameworkRepository::class);
        $fetchedFramework = $repository->getFrameworkByID($framework->id);

        $this->assertNotNull($fetchedFramework);
        $this->assertEquals('Test Framework', $fetchedFramework->name);
    }

    public function test_it_get_published_frameworks_with_type_filter(): void
    {
        Framework::factory()->create([
            'name' => 'Type A Framework',
            'type' => 'Type A',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Type B Framework',
            'type' => 'Type B',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Draft Type A Framework',
            'type' => 'Type A',
            'is_published' => false,
        ]);

        $repository = app(FrameworkRepository::class);
        $results = $repository->getPublishedFrameworks(['type' => 'Type A', 'per_page' => 10]);

        $this->assertCount(1, $results);
        $this->assertEquals('Type A Framework', $results->first()->name);
    }

    public function test_it_can_get_published_frameworks_with_authority_publisher_filter()
    {
        Framework::factory()->create([
            'name' => 'Type A Framework',
            'type' => 'Type A',
            'is_published' => true,
            'authority_publisher' => 'Publisher A',
        ]);

        Framework::factory()->create([
            'name' => 'Type B Framework',
            'type' => 'Type B',
            'is_published' => true,
            'authority_publisher' => 'Publisher B',
        ]);

        Framework::factory()->create([
            'name' => 'Draft Type A Framework',
            'type' => 'Type A',
            'is_published' => false,
            'authority_publisher' => 'Publisher A',
        ]);

        $repository = app(FrameworkRepository::class);
        $results = $repository->getPublishedFrameworks(['authority_publisher' => 'Publisher A']);

        $this->assertCount(1, $results);
        $this->assertEquals('Type A Framework', $results->first()->name);
    }

    public function test_it_can_get_published_frameworks_with_binding_level_filter()
    {
        Framework::factory()->create([
            'name' => 'Level 1 Framework',
            'binding_level' => 'Level 1',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Level 2 Framework',
            'binding_level' => 'Level 2',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Draft Level 1 Framework',
            'binding_level' => 'Level 1',
            'is_published' => false,
        ]);

        $repository = app(FrameworkRepository::class);
        $results = $repository->getPublishedFrameworks(['binding_level' => 'Level 1']);

        $this->assertCount(1, $results);
        $this->assertEquals('Level 1 Framework', $results->first()->name);
    }

    public function test_it_can_get_published_frameworks_with_sector_applicability_filter()
    {
        Framework::factory()->create([
            'name' => 'Finance Sector Framework',
            'sector_applicability' => 'Finance',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Healthcare Sector Framework',
            'sector_applicability' => 'Healthcare',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Draft Finance Sector Framework',
            'sector_applicability' => 'Finance',
            'is_published' => false,
        ]);

        $repository = app(FrameworkRepository::class);
        $results = $repository->getPublishedFrameworks(['sector_applicability' => 'Finance']);

        $this->assertCount(1, $results);
        $this->assertEquals('Finance Sector Framework', $results->first()->name);
    }

    public function test_it_can_get_published_frameworks_with_risk_class_coverage_filter()
    {
        Framework::factory()->create([
            'name' => 'High Risk Framework',
            'risk_class_coverage' => 'High',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Low Risk Framework',
            'risk_class_coverage' => 'Low',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Draft High Risk Framework',
            'risk_class_coverage' => 'High',
            'is_published' => false,
        ]);

        $repository = app(FrameworkRepository::class);
        $results = $repository->getPublishedFrameworks(['risk_class_coverage' => 'High']);

        $this->assertCount(1, $results);
        $this->assertEquals('High Risk Framework', $results->first()->name);
    }

    public function test_it_can_get_published_frameworks_with_certification_attestation_filter()
    {
        Framework::factory()->create([
            'name' => 'Certified Framework',
            'certification_attestation' => 'Certified',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Attested Framework',
            'certification_attestation' => 'Attested',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Draft Certified Framework',
            'certification_attestation' => 'Certified',
            'is_published' => false,
        ]);

        $repository = app(FrameworkRepository::class);
        $results = $repository->getPublishedFrameworks(['certification_attestation' => 'Certified']);

        $this->assertCount(1, $results);
        $this->assertEquals('Certified Framework', $results->first()->name);
    }

    public function test_it_can_get_published_frameworks_with_assessment_mode_filter()
    {
        Framework::factory()->create([
            'name' => 'Self-Assessment Framework',
            'assessment_mode' => 'Self-Assessment',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Third-Party Assessment Framework',
            'assessment_mode' => 'Third-Party',
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'name' => 'Draft Self-Assessment Framework',
            'assessment_mode' => 'Self-Assessment',
            'is_published' => false,
        ]);

        $repository = app(FrameworkRepository::class);
        $results = $repository->getPublishedFrameworks(['assessment_mode' => 'Self-Assessment']);

        $this->assertCount(1, $results);
        $this->assertEquals('Self-Assessment Framework', $results->first()->name);
    }

}

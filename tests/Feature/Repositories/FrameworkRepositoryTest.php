<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\Framework;
use App\Enums\Framework\Status;
use App\Repositories\FrameworkRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FrameworkRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private FrameworkRepository $frameworkRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->frameworkRepository = app(FrameworkRepository::class);
    }

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

        $results = $this->frameworkRepository->getFilteredFrameworks($user, ['name' => 'AI']);

        $this->assertCount(1, $results);
        $this->assertEquals('EU AI Act', $results->first()->name);
    }

    public function test_it_filter_frameworks_by_status(): void
    {
        $user = User::factory()->create();

        Framework::factory()->create([
            'user_id' => $user->id,
            'name' => 'Published Framework',
            'status' => Status::ACTIVE->value,
        ]);

        Framework::factory()->create([
            'user_id' => $user->id,
            'name' => 'Draft Framework',
            'status' => Status::DRAFT->value,
        ]);

        $results = $this->frameworkRepository->getFilteredFrameworks($user, ['status' => Status::ACTIVE->value]);

        $this->assertCount(1, $results);
        $this->assertEquals('Published Framework', $results->first()->name);
    }

    public function test_it_applies_pagination_correctly(): void
    {
        $user = User::factory()->create();

        Framework::factory()->count(15)->create([
            'user_id' => $user->id,
        ]);

        $results = $this->frameworkRepository->getFilteredFrameworks($user, ['per_page' => 5]);

        $this->assertCount(5, $results);
        $this->assertEquals(5, $results->perPage());
        $this->assertEquals(15, $results->total());
    }

    public function test_it_get_published_frameworks(): void
    {
        Framework::factory()->create([
            'name' => 'Published Framework 1',
            'effective_date' => now()->subDays(10),
        ]);

        Framework::factory()->create([
            'name' => 'Published Framework 2',
            'effective_date' => now()->subDays(5),
        ]);

        Framework::factory()->create([
            'name' => 'Draft Framework',
            'effective_date' => now()->addDays(5),
        ]);

        $results = $this->frameworkRepository->getPublishedFrameworks();

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
            'status' => Status::ACTIVE->value,
            'effective_date' => now()->subDays(10),
        ]);

        Framework::factory()->create([
            'name' => 'Type B Framework',
            'status' => Status::RETIRED->value,
            'effective_date' => now()->subDays(5),
        ]);

        Framework::factory()->create([
            'name' => 'Draft Type A Framework',
            'status' => Status::DRAFT->value,
            'effective_date' => now()->addDays(5),
        ]);

        $results = $this->frameworkRepository->getPublishedFrameworks(['status' => Status::ACTIVE->value, 'per_page' => 10]);

        $this->assertCount(1, $results);
        $this->assertEquals('Type A Framework', $results->first()->name);
    }

    public function test_it_creates_framework(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'New Framework',
            'version' => '1.0',
            'status' => Status::DRAFT->value,
            'effective_date' => now(),
            'jurisdictions' => json_encode(['US', 'EU']),
            'scope' => 'AI Systems',
            'source_url' => 'https://example.com/framework',
        ];

        $framework = $this->frameworkRepository->createForAdmin($user, $data);

        $this->assertNotNull($framework->id);
        $this->assertEquals('New Framework', $framework->name);
        $this->assertEquals(Status::DRAFT->value, $framework->status);
        $this->assertEquals($user->id, $framework->user_id);
        $this->assertDatabaseHas('frameworks', [
            'id' => $framework->id,
            'name' => 'New Framework',
            'user_id' => $user->id,
        ]);
    }
}

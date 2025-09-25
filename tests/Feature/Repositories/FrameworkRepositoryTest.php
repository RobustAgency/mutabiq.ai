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
        $results = $repository->getPublishedFrameworks(['per_page' => 10]);

        $this->assertCount(2, $results);
        $this->assertEquals(10, $results->perPage());
        $this->assertEquals(2, $results->total());
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
}

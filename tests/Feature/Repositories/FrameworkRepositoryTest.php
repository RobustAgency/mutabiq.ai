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

    public function test_it_can_filter_frameworks_by_name(): void
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

    public function test_it_can_filter_frameworks_by_status(): void
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

    public function test_it_can_get_available_frameworks(): void
    {
        $user = User::factory()->create();

        Framework::factory()->create([
            'user_id' => $user->id,
            'is_published' => true,
        ]);

        Framework::factory()->create([
            'user_id' => $user->id,
            'is_published' => false,
        ]);

        $repository = app(FrameworkRepository::class);
        $results = $repository->getAvailableFrameworks();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is_published);
    }
}

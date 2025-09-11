<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\Control;
use App\Repositories\ControlRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ControlRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_it_can_filter_controls_by_name(): void
    {
        $user = User::factory()->create();

        Control::factory()->create([
            'user_id' => $user->id,
            'name' => 'AI System Monitoring Control',
        ]);

        Control::factory()->create([
            'user_id' => $user->id,
            'name' => 'Data Privacy Control',
        ]);

        $repository = app(ControlRepository::class);
        $results = $repository->getFilteredControls($user, ['name' => 'AI']);

        $this->assertCount(1, $results);
        $this->assertEquals('AI System Monitoring Control', $results->first()->name); // 👈 fix here
    }
}

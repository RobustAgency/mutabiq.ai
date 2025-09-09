<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\Requirement;
use App\Repositories\RequirementRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequirementRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_it_can_filter_requirements_by_name(): void
    {
        $user = User::factory()->create();

        Requirement::factory()->create([
            'user_id' => $user->id,
            'name' => 'High Risk AI Requirement',
        ]);

        Requirement::factory()->create([
            'user_id' => $user->id,
            'name' => 'Data Governance Requirement',
        ]);

        $repository = app(RequirementRepository::class);
        $results = $repository->getFilteredRequirements($user, ['name' => 'AI']);

        $this->assertCount(1, $results);
        $this->assertEquals('High Risk AI Requirement', $results->first()->name);
    }
}

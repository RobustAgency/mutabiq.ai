<?php

namespace Tests\Feature\Repositories;

use App\Models\Tag;
use Tests\TestCase;
use App\Models\User;
use App\Repositories\TagRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_it_can_filter_tags_by_name(): void
    {
        $user = User::factory()->create();

        Tag::factory()->create([
            'user_id' => $user->id,
            'name' => 'High Risk AI Requirement',
        ]);
        Tag::factory()->count(2)->create([
            'user_id' => $user->id,
            'name' => 'Low Risk Requirement',
            'group' => 'General',
        ]);

        $repository = app(TagRepository::class);
        $results = $repository->getFilteredTagsForAdmin($user, ['term' => 'AI']);

        $this->assertCount(1, $results);
        $this->assertEquals('High Risk AI Requirement', $results->first()->name);
    }

    public function test_it_can_filter_tags_by_group(): void
    {
        $user = User::factory()->create();

        Tag::factory()->count(3)->create([
            'user_id' => $user->id,
            'name' => $this->faker->word(),
            'group' => 'AI',
        ]);
        Tag::factory()->count(2)->create([
            'user_id' => $user->id,
            'name' => 'Low Risk Requirement',
            'group' => 'General',
        ]);

        $repository = app(TagRepository::class);
        $results = $repository->getFilteredTagsForAdmin($user, ['term' => 'AI']);

        $this->assertCount(3, $results);
        $this->assertEquals('AI', $results->first()->group);
    }
}

<?php

namespace Tests\Feature\Controllers;

use App\Models\Tag;
use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Control;
use App\Models\Framework;
use App\Models\Requirement;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ControlControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_super_admin_can_list_their_controls(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        Control::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/admin/controls');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Controls retrieved successfully',
        ]);
    }

    public function test_super_admin_can_store_control(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $framework = Framework::factory()->create(['user_id' => $user->id]);
        $requirement = Requirement::factory()->create(['user_id' => $user->id]);
        $tag = Tag::factory()->create(['user_id' => $user->id]);

        $payload = [
            'name' => 'EU AI Act Control',
            'code' => 'CTRL-1',
            'question' => 'Have the AI risks been assessed?',
            'summary' => 'Ensure AI risks are documented and assessed.',
            'description' => 'Detailed description for this control.',
            'framework_ids' => [$framework->id],
            'requirement_ids' => [$requirement->id],
            'tag_ids' => [$tag->id],
        ];

        $response = $this->actingAs($user)->postJson('/api/admin/controls', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Control created successfully',
        ]);

        $this->assertDatabaseHas('controls', [
            'name' => 'EU AI Act Control',
            'code' => 'CTRL-1',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('control_framework', [
            'framework_id' => $framework->id,
        ]);

        $this->assertDatabaseHas('control_requirement', [
            'requirement_id' => $requirement->id,
        ]);

        $this->assertDatabaseHas('control_tag', [
            'tag_id' => $tag->id,
        ]);
    }

    public function test_super_admin_can_view_single_control(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $framework = Framework::factory()->create(['user_id' => $user->id]);
        $requirement = Requirement::factory()->create(['user_id' => $user->id]);
        $tag = Tag::factory()->create(['user_id' => $user->id]);

        $control = Control::factory()->create([
            'user_id' => $user->id,
            'name' => 'Sample Control',
            'code' => 'CTRL-100',
        ]);

        $control->frameworks()->attach($framework->id);
        $control->requirements()->attach($requirement->id);
        $control->tags()->attach($tag->id);

        $response = $this->actingAs($user)->getJson("/api/admin/controls/{$control->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Control retrieved successfully',
            'data' => [
                'id' => $control->id,
                'name' => 'Sample Control',
                'code' => 'CTRL-100',
            ],
        ]);
    }

    public function test_super_admin_can_update_control(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $framework1 = Framework::factory()->create(['user_id' => $user->id, 'name' => 'Framework 1']);
        $framework2 = Framework::factory()->create(['user_id' => $user->id, 'name' => 'Framework 2']);
        $requirement = Requirement::factory()->create(['user_id' => $user->id]);
        $tag = Tag::factory()->create(['user_id' => $user->id]);

        $control = Control::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old Control Title',
            'code' => 'CTRL-1',
        ]);

        $control->frameworks()->attach($framework2->id);
        $control->requirements()->attach($requirement->id);
        $control->tags()->attach($tag->id);

        $payload = [
            'name' => 'Updated Control Title',
            'code' => 'CTRL-2',
            'summary' => 'Updated summary for the control.',
            'framework_ids' => [$framework1->id, $framework2->id],
            'requirement_ids' => [$requirement->id],
            'tag_ids' => [$tag->id],
        ];

        $response = $this->actingAs($user)->postJson("/api/admin/controls/{$control->id}", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Control updated successfully',
        ]);

        $this->assertDatabaseHas('controls', [
            'id' => $control->id,
            'name' => 'Updated Control Title',
        ]);
    }

    public function test_super_admin_can_unlink_framework_from_control(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $framework1 = Framework::factory()->create(['user_id' => $user->id]);
        $framework2 = Framework::factory()->create(['user_id' => $user->id]);
        $control = Control::factory()->create(['user_id' => $user->id]);

        $control->frameworks()->attach([$framework1->id, $framework2->id]);

        $payload = [
            'name' => 'Updated Control',
            'code' => 'CTRL-1',
            'framework_ids' => [$framework1->id],
        ];

        $response = $this->actingAs($user)->postJson("/api/admin/controls/{$control->id}", $payload);

        $response->assertStatus(200);
        $control->refresh();

        $this->assertTrue($control->frameworks->contains($framework1->id));
        $this->assertFalse($control->frameworks->contains($framework2->id));
    }

    public function test_super_admin_can_unlink_requirement_from_control(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $requirement1 = Requirement::factory()->create(['user_id' => $user->id]);
        $requirement2 = Requirement::factory()->create(['user_id' => $user->id]);
        $control = Control::factory()->create(['user_id' => $user->id]);

        $control->requirements()->attach([$requirement1->id, $requirement2->id]);

        $payload = [
            'name' => 'Updated Control',
            'code' => 'CTRL-2',
            'requirement_ids' => [$requirement1->id],
        ];

        $response = $this->actingAs($user)->postJson("/api/admin/controls/{$control->id}", $payload);

        $response->assertStatus(200);
        $control->refresh();

        $this->assertTrue($control->requirements->contains($requirement1->id));
        $this->assertFalse($control->requirements->contains($requirement2->id));
    }

    public function test_super_admin_can_unlink_tag_from_control(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);
        $tag1 = Tag::factory()->create(['user_id' => $user->id]);
        $tag2 = Tag::factory()->create(['user_id' => $user->id]);
        $control = Control::factory()->create(['user_id' => $user->id]);

        $control->tags()->attach([$tag1->id, $tag2->id]);

        $payload = [
            'name' => 'Updated Control',
            'code' => 'CTRL-3',
            'tag_ids' => [$tag1->id],
        ];

        $response = $this->actingAs($user)->postJson("/api/admin/controls/{$control->id}", $payload);

        $response->assertStatus(200);
        $control->refresh();

        $this->assertTrue($control->tags->contains($tag1->id));
        $this->assertFalse($control->tags->contains($tag2->id));
    }
}

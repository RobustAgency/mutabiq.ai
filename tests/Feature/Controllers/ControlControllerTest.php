<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Control;
use App\Enums\Control\Status;
use App\Enums\Control\TestingMethod;
use App\Enums\Control\TestingFrequency;
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

        $payload = [
            'name' => 'EU AI Act Control',
            'reference' => 'CTRL-1',
            'objective' => 'Ensure compliance with EU AI regulations.',
            'testing_method' => TestingMethod::OPERATING->value,
            'testing_frequency' => TestingFrequency::QUARTERLY->value,
            'evidence_expectations' => 'Scan reports and logs.',
            'applicability_criteria' => 'Applies to all AI systems in production.',
            'status' => Status::PROPOSED->value,
            'last_test_date' => '2024-01-15',
            'next_test_due' => '2024-04-15',
        ];

        $response = $this->actingAs($user)->postJson('/api/admin/controls', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Control created successfully',
        ]);

        $this->assertDatabaseHas('controls', [
            'name' => 'EU AI Act Control',
            'reference' => 'CTRL-1',
            'user_id' => $user->id,
        ]);
    }

    public function test_super_admin_can_view_single_control(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        $control = Control::factory()->create([
            'user_id' => $user->id,
            'name' => 'Sample Control',
            'reference' => 'CTRL-100',
        ]);

        $response = $this->actingAs($user)->getJson("/api/admin/controls/{$control->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Control retrieved successfully',
            'data' => [
                'id' => $control->id,
                'name' => 'Sample Control',
                'reference' => 'CTRL-100',
            ],
        ]);
    }

    public function test_super_admin_can_update_control(): void
    {
        $user = User::factory()->create(['role' => UserRole::SUPER_ADMIN]);

        $control = Control::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old Control Title',
            'reference' => 'CTRL-1',
        ]);

        $payload = [
            'name' => 'Updated Control Title',
            'reference' => 'CTRL-2',
            'objective' => 'Updated summary for the control.',
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
}

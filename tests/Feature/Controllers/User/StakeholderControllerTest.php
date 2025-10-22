<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\Stakeholder\Type;
use App\Models\Stakeholder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StakeholderControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_get_paginated_stakeholders()
    {
        Stakeholder::factory()->count(15)->create();

        $response = $this->actingAs($this->user)->getJson('/api/stakeholders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data',
                    'total',
                    'per_page',
                ],
            ])
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholders retrieved successfully.',
            ]);

        $this->assertEquals(15, $response->json('data.total'));
        $this->assertEquals(10, $response->json('data.per_page'));
    }

    public function test_user_can_filter_stakeholders_by_type()
    {
        Stakeholder::factory()->count(5)->create(['type' => Type::PERSON->value]);
        Stakeholder::factory()->count(3)->create(['type' => Type::TEAM->value]);

        $response = $this->actingAs($this->user)->getJson('/api/stakeholders?type=' . Type::PERSON->value);

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.total'));
    }

    public function test_user_can_set_custom_per_page()
    {
        Stakeholder::factory()->count(20)->create();

        $response = $this->actingAs($this->user)->getJson('/api/stakeholders?per_page=5');

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('data.per_page'));
    }

    public function test_user_can_create_stakeholder()
    {
        $data = [
            'type' => Type::PERSON->value,
            'display_name' => 'John Doe',
            'email' => 'john@example.com',
            'timezone' => 'America/New_York',
            'classification' => 'internal',
            'active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/stakeholders', $data);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder created successfully.',
                'data' => [
                    'display_name' => 'John Doe',
                    'email' => 'john@example.com',
                    'type' => Type::PERSON->value,
                ],
            ]);

        $this->assertDatabaseHas('stakeholders', [
            'display_name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_user_cannot_create_stakeholder_without_required_fields()
    {
        $response = $this->actingAs($this->user)->postJson('/api/stakeholders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'display_name', 'timezone', 'classification', 'active']);
    }

    public function test_user_cannot_create_stakeholder_with_invalid_type()
    {
        $data = [
            'type' => 'invalid_type',
            'display_name' => 'John Doe',
            'timezone' => 'America/New_York',
            'classification' => 'internal',
            'active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/stakeholders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_user_cannot_create_stakeholder_with_invalid_email()
    {
        $data = [
            'type' => Type::PERSON->value,
            'display_name' => 'John Doe',
            'email' => 'invalid-email',
            'timezone' => 'America/New_York',
            'classification' => 'internal',
            'active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/stakeholders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_create_stakeholder_with_invalid_timezone()
    {
        $data = [
            'type' => Type::PERSON->value,
            'display_name' => 'John Doe',
            'timezone' => 'Invalid/Timezone',
            'classification' => 'internal',
            'active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/stakeholders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['timezone']);
    }

    public function test_user_can_view_stakeholder()
    {
        $stakeholder = Stakeholder::factory()->create([
            'display_name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/stakeholders/{$stakeholder->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder retrieved successfully.',
                'data' => [
                    'id' => $stakeholder->id,
                    'display_name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
            ]);
    }

    public function test_user_cannot_view_non_existent_stakeholder()
    {
        $response = $this->actingAs($this->user)->getJson('/api/stakeholders/99999');

        $response->assertStatus(404);
    }

    public function test_user_can_update_stakeholder()
    {
        $stakeholder = Stakeholder::factory()->create([
            'display_name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $updateData = [
            'type' => $stakeholder->type,
            'display_name' => 'Updated Name',
            'email' => 'updated@example.com',
            'timezone' => $stakeholder->timezone,
            'classification' => $stakeholder->classification,
            'active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson("/api/stakeholders/{$stakeholder->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder updated successfully.',
            ]);

        $this->assertDatabaseHas('stakeholders', [
            'id' => $stakeholder->id,
            'display_name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_cannot_update_stakeholder_without_required_fields()
    {
        $stakeholder = Stakeholder::factory()->create();

        $response = $this->actingAs($this->user)->postJson("/api/stakeholders/{$stakeholder->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'display_name', 'timezone', 'classification', 'active']);
    }

    public function test_user_cannot_update_non_existent_stakeholder()
    {
        $updateData = [
            'type' => Type::PERSON->value,
            'display_name' => 'Updated Name',
            'timezone' => 'America/New_York',
            'classification' => 'internal',
            'active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/stakeholders/99999', $updateData);

        $response->assertStatus(404);
    }

    public function test_user_can_create_stakeholder_with_all_fields()
    {
        $data = [
            'type' => Type::VENDOR_ORG->value,
            'display_name' => 'Acme Corp',
            'legal_name' => 'Acme Corporation Ltd',
            'org_unit' => 'Engineering',
            'email' => 'contact@acme.com',
            'phone' => '+1234567890',
            'role_tags' => ['admin', 'manager'],
            'timezone' => 'America/New_York',
            'classification' => 'external',
            'country' => 'US',
            'external_ref' => 'ext-123',
            'active' => true,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/stakeholders', $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('stakeholders', [
            'display_name' => 'Acme Corp',
            'legal_name' => 'Acme Corporation Ltd',
            'org_unit' => 'Engineering',
            'email' => 'contact@acme.com',
        ]);
    }

    public function test_user_can_delete_stakeholder()
    {
        $stakeholder = Stakeholder::factory()->create([
            'display_name' => 'John Doe',
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/stakeholders/{$stakeholder->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder deleted successfully.',
            ]);

        $this->assertDatabaseMissing('stakeholders', [
            'id' => $stakeholder->id,
        ]);
    }

    public function test_user_cannot_delete_non_existent_stakeholder()
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/stakeholders/99999');

        $response->assertStatus(404);
    }

    public function test_guest_cannot_list_stakeholders()
    {
        $response = $this->getJson('/api/stakeholders');

        $response->assertStatus(401);
    }

    public function test_guest_cannot_create_stakeholder()
    {
        $data = [
            'type' => Type::PERSON->value,
            'display_name' => 'John Doe',
            'timezone' => 'America/New_York',
            'classification' => 'internal',
            'active' => true,
        ];

        $response = $this->postJson('/api/stakeholders', $data);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_view_stakeholder()
    {
        $stakeholder = Stakeholder::factory()->create();

        $response = $this->getJson("/api/stakeholders/{$stakeholder->id}");

        $response->assertStatus(401);
    }

    public function test_guest_cannot_update_stakeholder()
    {
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'type' => Type::PERSON->value,
            'display_name' => 'Updated Name',
            'timezone' => 'America/New_York',
            'classification' => 'internal',
            'active' => true,
        ];

        $response = $this->postJson("/api/stakeholders/{$stakeholder->id}", $data);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_delete_stakeholder()
    {
        $stakeholder = Stakeholder::factory()->create();

        $response = $this->deleteJson("/api/stakeholders/{$stakeholder->id}");

        $response->assertStatus(401);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Stakeholder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StakeholderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_paginated_stakeholders(): void
    {
        Stakeholder::factory()->count(5)->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholders retrieved successfully.',
            ])
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'type',
                            'display_name',
                            'legal_name',
                            'email',
                            'phone',
                            'org_unit',
                            'vendor_id',
                            'role_tags',
                            'timezone',
                            'classification',
                            'country',
                            'external_ref',
                            'active',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_index_search_by_display_name(): void
    {
        Stakeholder::factory()->create(['display_name' => 'John Smith']);
        Stakeholder::factory()->create(['display_name' => 'Jane Doe']);
        Stakeholder::factory()->create(['display_name' => 'Alice Johnson']);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?search=John');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(2, $data); // John Smith and Alice Johnson
        $displayNames = collect($data)->pluck('display_name')->toArray();
        $this->assertTrue(in_array('John Smith', $displayNames));
        $this->assertTrue(in_array('Alice Johnson', $displayNames));
        $this->assertFalse(in_array('Jane Doe', $displayNames));
    }

    public function test_index_search_by_legal_name(): void
    {
        Stakeholder::factory()->create([
            'display_name' => 'Person A',
            'legal_name' => 'Acme Corporation',
        ]);
        Stakeholder::factory()->create([
            'display_name' => 'Person B',
            'legal_name' => 'Beta Industries',
        ]);
        Stakeholder::factory()->create([
            'display_name' => 'Person C',
            'legal_name' => 'Acme Solutions',
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?search=Acme');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(2, $data);
        $legalNames = collect($data)->pluck('legal_name')->toArray();
        $this->assertTrue(in_array('Acme Corporation', $legalNames));
        $this->assertTrue(in_array('Acme Solutions', $legalNames));
        $this->assertFalse(in_array('Beta Industries', $legalNames));
    }

    public function test_index_search_by_email(): void
    {
        Stakeholder::factory()->create(['email' => 'john@example.com']);
        Stakeholder::factory()->create(['email' => 'jane@example.com']);
        Stakeholder::factory()->create(['email' => 'admin@company.com']);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?search=example.com');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(2, $data);
        $emails = collect($data)->pluck('email')->toArray();
        $this->assertTrue(in_array('john@example.com', $emails));
        $this->assertTrue(in_array('jane@example.com', $emails));
        $this->assertFalse(in_array('admin@company.com', $emails));
    }

    public function test_index_search_is_case_insensitive(): void
    {
        Stakeholder::factory()->create(['display_name' => 'John Smith']);
        Stakeholder::factory()->create(['display_name' => 'JANE DOE']);
        Stakeholder::factory()->create(['display_name' => 'alice johnson']);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?search=JOHN');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(2, $data); // John Smith and alice johnson
    }

    public function test_index_search_partial_match(): void
    {
        Stakeholder::factory()->create(['display_name' => 'Robert Johnson']);
        Stakeholder::factory()->create(['display_name' => 'Rob Smith']);
        Stakeholder::factory()->create(['display_name' => 'Alice Brown']);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?search=Rob');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(2, $data);
        $displayNames = collect($data)->pluck('display_name')->toArray();
        $this->assertTrue(in_array('Robert Johnson', $displayNames));
        $this->assertTrue(in_array('Rob Smith', $displayNames));
    }

    public function test_index_search_returns_empty_when_no_match(): void
    {
        Stakeholder::factory()->create(['display_name' => 'John Smith']);
        Stakeholder::factory()->create(['display_name' => 'Jane Doe']);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?search=NonExistentName');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(0, $data);
    }

    public function test_index_search_across_multiple_fields(): void
    {
        Stakeholder::factory()->create([
            'display_name' => 'John Smith',
            'legal_name' => 'Tech Corp',
            'email' => 'john@techcorp.com',
        ]);
        Stakeholder::factory()->create([
            'display_name' => 'Jane Doe',
            'legal_name' => 'Tech Solutions',
            'email' => 'jane@solutions.com',
        ]);
        Stakeholder::factory()->create([
            'display_name' => 'Bob Wilson',
            'legal_name' => 'Other Company',
            'email' => 'bob@other.com',
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?search=Tech');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(2, $data);
        $displayNames = collect($data)->pluck('display_name')->toArray();
        $this->assertTrue(in_array('John Smith', $displayNames));
        $this->assertTrue(in_array('Jane Doe', $displayNames));
        $this->assertFalse(in_array('Bob Wilson', $displayNames));
    }

    public function test_index_filter_by_type(): void
    {
        Stakeholder::factory()->create(['type' => 'internal']);
        Stakeholder::factory()->create(['type' => 'external']);
        Stakeholder::factory()->create(['type' => 'internal']);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?type=internal');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(2, $data);
        $types = collect($data)->pluck('type')->unique()->toArray();
        $this->assertEquals(['internal'], $types);
    }

    public function test_index_filter_by_type_and_search_combined(): void
    {
        Stakeholder::factory()->create([
            'type' => 'internal',
            'display_name' => 'John Smith',
        ]);
        Stakeholder::factory()->create([
            'type' => 'external',
            'display_name' => 'John Doe',
        ]);
        Stakeholder::factory()->create([
            'type' => 'internal',
            'display_name' => 'Alice Johnson',
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?type=internal&search=John');

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(2, $data);
        $this->assertEquals('John Smith', $data[0]['display_name']);
        $this->assertEquals('internal', $data[0]['type']);
    }

    public function test_index_custom_per_page(): void
    {
        Stakeholder::factory()->count(15)->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?per_page=5');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 5)
            ->assertJsonPath('data.total', 15);

        $data = $response->json('data.data');
        $this->assertCount(5, $data);
    }

    public function test_index_validates_per_page_minimum(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?per_page=0');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_validates_per_page_maximum(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?per_page=101');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_search_with_special_characters(): void
    {
        Stakeholder::factory()->create(['display_name' => "O'Brien"]);
        Stakeholder::factory()->create(['display_name' => 'Smith & Jones']);
        Stakeholder::factory()->create(['display_name' => 'Regular Name']);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/stakeholders?search=" . urlencode("O'Brien"));

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertGreaterThanOrEqual(1, count($data));
    }

    public function test_index_search_with_whitespace(): void
    {
        Stakeholder::factory()->create(['display_name' => 'John Smith']);
        Stakeholder::factory()->create(['display_name' => 'Jane Doe']);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders?search=' . urlencode('John Smith'));

        $response->assertStatus(200);
        $data = $response->json('data.data');

        $this->assertCount(1, $data);
        $this->assertEquals('John Smith', $data[0]['display_name']);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/stakeholders');

        $response->assertStatus(401);
    }

    public function test_store_creates_stakeholder(): void
    {
        $data = [
            'type' => 'vendor_org',
            'display_name' => 'John Smith',
            'legal_name' => 'Tech Company',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'org_unit' => 'Engineering',
            'role_tags' => ['admin', 'developer'],
            'timezone' => 'America/New_York',
            'classification' => 'internal',
            'country' => 'US',
            'active' => true,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/stakeholders', $data);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder created successfully.',
            ])
            ->assertJsonPath('data.display_name', 'John Smith')
            ->assertJsonPath('data.email', 'john@example.com');

        $this->assertDatabaseHas('stakeholders', [
            'display_name' => 'John Smith',
            'email' => 'john@example.com',
        ]);
    }

    public function test_show_returns_stakeholder(): void
    {
        $stakeholder = Stakeholder::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/stakeholders/{$stakeholder->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder retrieved successfully.',
            ])
            ->assertJsonPath('data.id', $stakeholder->id)
            ->assertJsonPath('data.display_name', $stakeholder->display_name);
    }

    public function test_show_returns_404_for_non_existent_stakeholder(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/stakeholders/99999');

        $response->assertStatus(404);
    }

    public function test_update_updates_stakeholder(): void
    {
        $stakeholder = Stakeholder::factory()->create([
            'display_name' => 'Original Name',
        ]);

        $updateData = [
            'type' => $stakeholder->type,
            'display_name' => 'Updated Name',
            'email' => $stakeholder->email,
            'timezone' => $stakeholder->timezone,
            'classification' => $stakeholder->classification,
            'active' => $stakeholder->active,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/stakeholders/{$stakeholder->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder updated successfully.',
            ]);

        $this->assertDatabaseHas('stakeholders', [
            'id' => $stakeholder->id,
            'display_name' => 'Updated Name',
        ]);
    }

    public function test_destroy_deletes_stakeholder(): void
    {
        $stakeholder = Stakeholder::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/stakeholders/{$stakeholder->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Stakeholder deleted successfully.',
            ]);

        $this->assertDatabaseMissing('stakeholders', [
            'id' => $stakeholder->id,
        ]);
    }

    public function test_destroy_returns_404_for_non_existent_stakeholder(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson('/api/stakeholders/99999');

        $response->assertStatus(404);
    }
}

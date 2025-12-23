<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\AiAsset;
use App\Models\Agreement;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiAssetControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_index_returns_paginated_ai_assets(): void
    {
        AiAsset::factory()->count(15)->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-assets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'vendor_id',
                            'vendor_effective_from',
                            'vendor_effective_to',
                            'vendor_agreement_id',
                            'vendor_assessment_id',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_index_returns_default_pagination(): void
    {
        AiAsset::factory()->count(20)->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-assets');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 15);
    }
    public function test_index_accepts_custom_per_page(): void
    {
        AiAsset::factory()->count(20)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-assets?per_page=5');
        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 5)
            ->assertJsonCount(5, 'data.data');
    }

    public function test_index_includes_vendor_relationship(): void
    {
        $vendor = Vendor::factory()->create(['vendor_name' => 'Test Vendor']);
        AiAsset::factory()->create([
            'organization_id' => $this->organization->id,
            'vendor_id' => $vendor->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-assets');

        $response->assertStatus(200)
            ->assertJsonPath('data.data.0.vendor.vendor_name', 'Test Vendor');
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/ai-assets');

        $response->assertStatus(401);
    }

    public function test_store_creates_ai_asset_with_vendor(): void
    {
        $vendor = Vendor::factory()->create();
        $data = [
            'vendor_id' => $vendor->id,
            'vendor_effective_from' => now()->subMonth()->toDateString(),
            'vendor_effective_to' => now()->addYear()->toDateString(),
            'vendor_assessment_id' => 123,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-assets', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'AI asset created successfully',
            ])
            ->assertJsonPath('data.vendor_id', $vendor->id)
            ->assertJsonPath('data.vendor_assessment_id', 123);

        $this->assertDatabaseHas('ai_assets', [
            'vendor_id' => $vendor->id,
            'vendor_assessment_id' => 123,
        ]);
    }

    public function test_store_creates_ai_asset_without_vendor(): void
    {
        $data = [
            'vendor_id' => null,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-assets', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'AI asset created successfully',
            ])
            ->assertJsonPath('data.vendor_id', null);

        $this->assertDatabaseHas('ai_assets', [
            'vendor_id' => null,
        ]);
    }

    public function test_store_creates_ai_asset_with_agreement(): void
    {
        $vendor = Vendor::factory()->create();
        $agreement = Agreement::factory()->create(['vendor_id' => $vendor->id]);

        $data = [
            'vendor_id' => $vendor->id,
            'vendor_agreement_id' => $agreement->id,
            'vendor_effective_from' => now()->subMonth()->toDateString(),
            'vendor_effective_to' => now()->addYear()->toDateString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-assets', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.vendor_agreement_id', $agreement->id);

        $this->assertDatabaseHas('ai_assets', [
            'vendor_id' => $vendor->id,
            'vendor_agreement_id' => $agreement->id,
        ]);
    }

    public function test_store_validates_vendor_exists(): void
    {
        $data = [
            'vendor_id' => 99999,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-assets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_id']);
    }

    public function test_store_validates_vendor_agreement_exists(): void
    {
        $vendor = Vendor::factory()->create();
        $data = [
            'vendor_id' => $vendor->id,
            'vendor_agreement_id' => 99999,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-assets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_agreement_id']);
    }

    public function test_store_validates_effective_to_after_effective_from(): void
    {
        $vendor = Vendor::factory()->create();
        $data = [
            'vendor_id' => $vendor->id,
            'vendor_effective_from' => now()->addYear()->toDateString(),
            'vendor_effective_to' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-assets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_effective_to']);
    }

    public function test_store_validates_vendor_assessment_id_is_integer(): void
    {
        $vendor = Vendor::factory()->create();
        $data = [
            'vendor_id' => $vendor->id,
            'vendor_assessment_id' => 'not-an-integer',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-assets', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_assessment_id']);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/ai-assets', []);

        $response->assertStatus(401);
    }

    public function test_show_returns_ai_asset(): void
    {
        $aiAsset = AiAsset::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/ai-assets/{$aiAsset->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI asset retrieved successfully',
            ])
            ->assertJsonPath('data.id', $aiAsset->id);
    }

    public function test_show_includes_vendor_relationship(): void
    {
        $vendor = Vendor::factory()->create(['vendor_name' => 'Test Vendor']);
        $aiAsset = AiAsset::factory()->create(['vendor_id' => $vendor->id]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/ai-assets/{$aiAsset->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.vendor.vendor_name', 'Test Vendor');
    }

    public function test_show_returns_404_for_non_existent_ai_asset(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-assets/99999');

        $response->assertStatus(404);
    }

    public function test_show_requires_authentication(): void
    {
        $aiAsset = AiAsset::factory()->create();

        $response = $this->getJson("/api/ai-assets/{$aiAsset->id}");

        $response->assertStatus(401);
    }

    public function test_update_modifies_ai_asset(): void
    {
        $aiAsset = AiAsset::factory()->create(['vendor_assessment_id' => 100]);
        $updateData = ['vendor_assessment_id' => 200];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-assets/{$aiAsset->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI asset updated successfully',
            ])
            ->assertJsonPath('data.vendor_assessment_id', 200);

        $this->assertDatabaseHas('ai_assets', [
            'id' => $aiAsset->id,
            'vendor_assessment_id' => 200,
        ]);
    }

    public function test_update_can_add_vendor(): void
    {
        $aiAsset = AiAsset::factory()->withoutVendor()->create();
        $vendor = Vendor::factory()->create();

        $updateData = [
            'vendor_id' => $vendor->id,
            'vendor_effective_from' => now()->subMonth()->toDateString(),
            'vendor_effective_to' => now()->addYear()->toDateString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-assets/{$aiAsset->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.vendor_id', $vendor->id);

        $this->assertDatabaseHas('ai_assets', [
            'id' => $aiAsset->id,
            'vendor_id' => $vendor->id,
        ]);
    }

    public function test_update_can_remove_vendor(): void
    {
        $vendor = Vendor::factory()->create();
        $aiAsset = AiAsset::factory()->create(['vendor_id' => $vendor->id]);

        $updateData = [
            'vendor_id' => null,
            'vendor_effective_from' => null,
            'vendor_effective_to' => null,
            'vendor_agreement_id' => null,
            'vendor_assessment_id' => null,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-assets/{$aiAsset->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.vendor_id', null);

        $this->assertDatabaseHas('ai_assets', [
            'id' => $aiAsset->id,
            'vendor_id' => null,
        ]);
    }

    public function test_update_can_change_vendor_agreement(): void
    {
        $vendor = Vendor::factory()->create();
        $agreement1 = Agreement::factory()->create(['vendor_id' => $vendor->id]);
        $agreement2 = Agreement::factory()->create(['vendor_id' => $vendor->id]);

        $aiAsset = AiAsset::factory()->create([
            'vendor_id' => $vendor->id,
            'vendor_agreement_id' => $agreement1->id,
        ]);

        $updateData = ['vendor_agreement_id' => $agreement2->id];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-assets/{$aiAsset->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.vendor_agreement_id', $agreement2->id);

        $this->assertDatabaseHas('ai_assets', [
            'id' => $aiAsset->id,
            'vendor_agreement_id' => $agreement2->id,
        ]);
    }

    public function test_update_validates_vendor_exists(): void
    {
        $aiAsset = AiAsset::factory()->create();
        $updateData = ['vendor_id' => 99999];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-assets/{$aiAsset->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_id']);
    }

    public function test_update_validates_vendor_agreement_exists(): void
    {
        $vendor = Vendor::factory()->create();
        $aiAsset = AiAsset::factory()->create(['vendor_id' => $vendor->id]);
        $updateData = ['vendor_agreement_id' => 99999];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-assets/{$aiAsset->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_agreement_id']);
    }

    public function test_update_validates_effective_to_after_effective_from(): void
    {
        $vendor = Vendor::factory()->create();
        $aiAsset = AiAsset::factory()->create(['vendor_id' => $vendor->id]);

        $updateData = [
            'vendor_effective_from' => now()->addYear()->toDateString(),
            'vendor_effective_to' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-assets/{$aiAsset->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vendor_effective_to']);
    }

    public function test_update_requires_authentication(): void
    {
        $aiAsset = AiAsset::factory()->create();

        $response = $this->postJson("/api/ai-assets/{$aiAsset->id}", []);

        $response->assertStatus(401);
    }

    public function test_destroy_deletes_ai_asset(): void
    {
        $aiAsset = AiAsset::factory()->create();

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/ai-assets/{$aiAsset->id}");

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'AI asset deleted successfully',
            ]);

        $this->assertDatabaseMissing('ai_assets', [
            'id' => $aiAsset->id,
        ]);
    }

    public function test_destroy_preserves_vendor_when_deleting_ai_asset(): void
    {
        $vendor = Vendor::factory()->create();
        $aiAsset = AiAsset::factory()->create(['vendor_id' => $vendor->id]);

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/ai-assets/{$aiAsset->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('ai_assets', ['id' => $aiAsset->id]);
        $this->assertDatabaseHas('vendors', ['id' => $vendor->id]);
    }

    public function test_destroy_preserves_agreement_when_deleting_ai_asset(): void
    {
        $vendor = Vendor::factory()->create();
        $agreement = Agreement::factory()->create(['vendor_id' => $vendor->id]);
        $aiAsset = AiAsset::factory()->create([
            'vendor_id' => $vendor->id,
            'vendor_agreement_id' => $agreement->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/ai-assets/{$aiAsset->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('ai_assets', ['id' => $aiAsset->id]);
        $this->assertDatabaseHas('agreements', ['id' => $agreement->id]);
    }

    public function test_destroy_returns_404_for_non_existent_ai_asset(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson('/api/ai-assets/99999');

        $response->assertStatus(404);
    }

    public function test_destroy_requires_authentication(): void
    {
        $aiAsset = AiAsset::factory()->create();

        $response = $this->deleteJson("/api/ai-assets/{$aiAsset->id}");

        $response->assertStatus(401);
    }

    public function test_store_accepts_all_nullable_fields_as_null(): void
    {
        $data = [
            'vendor_id' => null,
            'vendor_effective_from' => null,
            'vendor_effective_to' => null,
            'vendor_agreement_id' => null,
            'vendor_assessment_id' => null,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-assets', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('ai_assets', [
            'vendor_id' => null,
            'vendor_effective_from' => null,
            'vendor_effective_to' => null,
            'vendor_agreement_id' => null,
            'vendor_assessment_id' => null,
        ]);
    }
}

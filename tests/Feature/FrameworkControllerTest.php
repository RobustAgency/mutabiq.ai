<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Enums\UserRole;
use App\Models\Framework;
use App\Enums\FrameworkCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FrameworkControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_admin_can_list_their_frameworks(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);

        Framework::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/admin/frameworks');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Frameworks retrieved successfully',
        ]);
    }

    public function test_admin_can_store_framework(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);

        $payload = [
            'name' => 'EU AI Act',
            'code' => 'MFF-1',
            'type' => 'Law/Act',
            'geography' => 'EU',
            'category' => FrameworkCategory::Mandatory->value,
            'version' => '1.0',
            'release_date' => now()->toDateTimeString(),
            'is_published' => true,
            'description' => 'Comprehensive regulation for governing AI systems in the EU.',
            'authority_publisher' => 'European Commission',
            'binding_level' => 'Legally Binding',
            'sector_applicability' => 'Cross-sector (Healthcare, Finance, Education)',
            'risk_class_coverage' => 'High-Risk AI, Prohibited Uses',
            'certification_attestation' => 'Notified Body Required',
            'assessment_mode' => 'Third-party Assessment',
        ];

        $response = $this->actingAs($user)->postJson('/api/admin/frameworks', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'error' => false,
            'message' => 'Framework created successfully',
        ]);

        $this->assertDatabaseHas('frameworks', [
            'name' => 'EU AI Act',
            'user_id' => $user->id,
        ]);
    }

    public function test_admin_can_store_framework_with_logo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => UserRole::ADMIN]);

        $payload = [
            'name' => 'EU AI Act',
            'code' => 'MFF-1',
            'type' => 'Law/Act',
            'geography' => 'EU',
            'version' => '1.0',
            'release_date' => now()->toDateTimeString(),
            'is_published' => true,
            'client_logo' => UploadedFile::fake()->image('logo.png'),
            'category' => FrameworkCategory::Voluntary->value,
        ];

        $response = $this->actingAs($user)->post('/api/admin/frameworks', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('frameworks', ['name' => 'EU AI Act']);

        $framework = Framework::first();

        $this->assertNotNull($framework);
        $this->assertNotNull($framework->getFirstMediaUrl('client_logo'));
    }

    public function test_admin_can_view_single_framework(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);
        $framework = Framework::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson("/api/admin/frameworks/{$framework->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Framework retrieved successfully',
            'data' => ['id' => $framework->id],
        ]);
    }

    public function test_admin_can_update_framework(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);
        $framework = Framework::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

        $payload = [
            'name' => 'Updated Framework Name',
            'code' => 'MFF-1',
            'type' => 'Law/Act',
            'geography' => 'EU',
            'category' => FrameworkCategory::Voluntary->value,
            'version' => '1.0',
            'release_date' => now()->toDateTimeString(),
            'is_published' => true,
            'description' => 'Comprehensive regulation for governing AI systems in the EU.',
            'authority_publisher' => 'European Commission',
            'binding_level' => 'Legally Binding',
            'sector_applicability' => 'Cross-sector (Healthcare, Finance, Education)',
            'risk_class_coverage' => 'High-Risk AI, Prohibited Uses',
            'certification_attestation' => 'Notified Body Required',
            'assessment_mode' => 'Third-party Assessment',
        ];

        $response = $this->actingAs($user)->putJson("/api/admin/frameworks/{$framework->id}", $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'error' => false,
            'message' => 'Framework updated successfully',
            'data' => null,
        ]);

        $this->assertDatabaseHas('frameworks', [
            'id' => $framework->id,
            'name' => 'Updated Framework Name',
        ]);
    }

    public function test_admin_can_replace_framework_logo_on_update(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => UserRole::ADMIN]);

        $framework = Framework::factory()->create(['user_id' => $user->id]);

        $framework->addMedia(UploadedFile::fake()->image('old_logo.png'))->toMediaCollection('framework_logos');

        $payload = [
            'name' => 'With New Logo',
            'launch_date' => now()->toDateTimeString(),
            'framework_logo' => UploadedFile::fake()->image('new_logo.png'),
        ];

        $response = $this->actingAs($user)->putJson("/api/admin/frameworks/{$framework->id}", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('frameworks', [
            'id' => $framework->id,
            'name' => 'With New Logo',
        ]);

        $freshFramework = $framework->fresh();

        $this->assertCount(1, $freshFramework->getMedia('framework_logos'));
        $this->assertStringContainsString(
            'new_logo',
            $freshFramework->getFirstMediaUrl('framework_logos')
        );
    }
}

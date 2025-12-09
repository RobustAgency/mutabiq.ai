<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\RiskMethodology;
use App\Enums\RiskMethodology\ImpactScale;
use Illuminate\Foundation\Testing\WithFaker;
use App\Enums\RiskMethodology\LikelihoodScale;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RiskMethodologyControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    public function test_index_returns_paginated_risk_methodologies(): void
    {
        RiskMethodology::factory(3)->create(['organization_id' => $this->organization->id]);
        RiskMethodology::factory(2)->create(); // Different organization

        $response = $this->actingAs($this->user)->getJson('/api/risk-methodologies');
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data',
                    'current_page',
                    'total',
                ],
                'message',
                'error',
            ]);

        $this->assertEquals(3, $response->json('data.total'));
    }

    public function test_index_filters_by_name(): void
    {
        RiskMethodology::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Risk Assessment Framework',
        ]);
        RiskMethodology::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Compliance Policy',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/risk-methodologies?name=Risk');

        $response->assertOk();
        $this->assertEquals(1, $response->json('data.total'));
        $this->assertStringContainsString('Risk', $response->json('data.data.0.name'));
    }

    public function test_index_filters_by_effective_dates(): void
    {
        RiskMethodology::factory()->create([
            'organization_id' => $this->organization->id,
            'effective_from' => '2025-01-01',
        ]);
        RiskMethodology::factory()->create([
            'organization_id' => $this->organization->id,
            'effective_from' => '2025-06-01',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/risk-methodologies?effective_from=2025-05-01');

        $response->assertOk();
        $this->assertEquals(1, $response->json('data.total'));
    }

    public function test_store_creates_risk_methodology(): void
    {
        $data = [
            'name' => 'New Risk Methodology',
            'likelihood_scale' => LikelihoodScale::RARE->value,
            'impact_scale' => ImpactScale::MINOR->value,
            'matrix_rule' => [
                'rare' => ['minor' => 'low', 'moderate' => 'low', 'major' => 'medium'],
                'possible' => ['minor' => 'low', 'moderate' => 'medium', 'major' => 'high'],
                'likely' => ['minor' => 'medium', 'moderate' => 'high', 'major' => 'high'],
            ],
            'acceptance_thresholds' => 'hola',
            'aggregation_logic' => 'mean',
            'review_policy' => 'Annual review required',
            'effective_from' => '2025-01-01',
            'owner_team' => 'Risk Management Team',
            'source_created_at' => now(),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/risk-methodologies', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'likelihood_scale',
                    'impact_scale',
                    'matrix_rule',
                    'organization_id',
                ],
                'message',
                'error',
            ]);

        $this->assertDatabaseHas('risk_methodologies', [
            'name' => $data['name'],
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_store_validation_fails_with_missing_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/risk-methodologies', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_show_returns_risk_methodology(): void
    {
        $methodology = RiskMethodology::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson("/api/risk-methodologies/{$methodology->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'likelihood_scale',
                    'impact_scale',
                    'organization',
                ],
                'message',
                'error',
            ]);

        $this->assertEquals($methodology->id, $response->json('data.id'));
    }

    public function test_show_returns_404_for_nonexistent_methodology(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/risk-methodologies/99999');

        $response->assertNotFound();
    }

    public function test_update_modifies_risk_methodology(): void
    {
        $methodology = RiskMethodology::factory()->create(['organization_id' => $this->organization->id]);
        $newName = 'Updated Methodology';

        $response = $this->actingAs($this->user)->postJson("/api/risk-methodologies/{$methodology->id}", [
            'name' => $newName,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                ],
                'message',
                'error',
            ]);

        $this->assertEquals($newName, $response->json('data.name'));
        $this->assertDatabaseHas('risk_methodologies', [
            'id' => $methodology->id,
            'name' => $newName,
        ]);
    }

    public function test_update_validation_fails_with_invalid_data(): void
    {
        $methodology = RiskMethodology::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->postJson("/api/risk-methodologies/{$methodology->id}", [
            'name' => '', // Invalid: empty name
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_destroy_deletes_risk_methodology(): void
    {
        $methodology = RiskMethodology::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/risk-methodologies/{$methodology->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'error',
            ]);

        $this->assertDatabaseMissing('risk_methodologies', ['id' => $methodology->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_methodology(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/risk-methodologies/99999');

        $response->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_access_endpoints(): void
    {
        $methodology = RiskMethodology::factory()->create();

        $this->getJson('/api/risk-methodologies')->assertUnauthorized();
        $this->postJson('/api/risk-methodologies', [])->assertUnauthorized();
        $this->getJson("/api/risk-methodologies/{$methodology->id}")->assertUnauthorized();
        $this->postJson("/api/risk-methodologies/{$methodology->id}", [])->assertUnauthorized();
        $this->deleteJson("/api/risk-methodologies/{$methodology->id}")->assertUnauthorized();
    }

    public function test_user_can_only_access_own_organization_methodologies(): void
    {
        $otherOrganization = Organization::factory()->create();
        $otherMethodology = RiskMethodology::factory()->create(['organization_id' => $otherOrganization->id]);

        // Should not find methodology from other organization in index
        $response = $this->actingAs($this->user)->getJson('/api/risk-methodologies');
        $response->assertOk();
        $this->assertEquals(0, $response->json('data.total'));
    }

    public function test_index_pagination_works_correctly(): void
    {
        RiskMethodology::factory(25)->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson('/api/risk-methodologies?per_page=10');

        $response->assertOk();
        $this->assertEquals(10, count($response->json('data.data')));
        $this->assertEquals(25, $response->json('data.total'));
        $this->assertTrue($response->json('data.current_page') === 1);
    }

    public function test_store_sets_organization_id_from_authenticated_user(): void
    {
        $data = [
            'name' => 'Test Methodology',
            'likelihood_scale' => LikelihoodScale::POSSIBLE->value,
            'impact_scale' => ImpactScale::MINOR->value,
            'aggregation_logic' => 'mean',
            'matrix_rule' => [
                'low' => ['low' => 'low', 'medium' => 'medium', 'high' => 'high'],
                'medium' => ['low' => 'medium', 'medium' => 'high', 'high' => 'high'],
                'high' => ['low' => 'high', 'medium' => 'high', 'high' => 'high'],
            ],
            'review_policy' => 'Quarterly review',
            'owner_team' => $this->user->name,
            'source_created_at' => now(),
            'acceptance_thresholds' => 'hola',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/risk-methodologies', $data);

        $response->assertStatus(201);
        $this->assertEquals($this->organization->id, $response->json('data.organization_id'));
    }
}

<?php

namespace Tests\Feature\Controllers\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Models\AiRiskRegister;
use App\Models\AiRiskTreatment;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiRiskTreatmentControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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

    public function test_index_returns_only_organization_treatments(): void
    {
        AiRiskTreatment::factory()->count(3)->create(['organization_id' => $this->organization->id]);
        AiRiskTreatment::factory()->count(2)->create(); // other org

        $response = $this->actingAs($this->user)->getJson('/api/ai-risk-treatments');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['data', 'total', 'current_page'], 'message', 'error']);

        $this->assertEquals(3, $response->json('data.total'));
    }

    public function test_store_validates_and_creates_treatment(): void
    {
        $stakeholder = Stakeholder::factory()->create(['organization_id' => $this->organization->id]);
        $aiRisk = AiRiskRegister::factory()->create(['organization_id' => $this->organization->id]);

        $payload = [
            'ai_risk_register_id' => $aiRisk->id,
            'treatment_type' => 'mitigation',
            'plan_summary' => 'Mitigate by retraining with balanced dataset',
            'owner_stakeholder_id' => $stakeholder->id,
            'assignee' => ['Alice'],
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'status' => 'open',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/ai-risk-treatments', $payload);

        // Controller returns 200 with data on success
        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'organization_id', 'status'], 'message', 'error']);

        $this->assertDatabaseHas('ai_risk_treatments', [
            'organization_id' => $this->organization->id,
            'plan_summary' => 'Mitigate by retraining with balanced dataset',
            'status' => 'open',
        ]);
    }

    public function test_store_validation_fails_for_missing_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/ai-risk-treatments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'ai_risk_register_id',
                'treatment_type',
                'plan_summary',
                'owner_stakeholder_id',
                'due_date',
                'status',
            ]);
    }

    public function test_show_returns_treatment(): void
    {
        $treatment = AiRiskTreatment::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->getJson("/api/ai-risk-treatments/{$treatment->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $treatment->id);
    }

    public function test_update_is_forbidden_by_request_authorization(): void
    {
        $treatment = AiRiskTreatment::factory()->create(['organization_id' => $this->organization->id, 'status' => 'open']);

        $response = $this->actingAs($this->user)->postJson("/api/ai-risk-treatments/{$treatment->id}", [
            'status' => 'in_progress',
        ]);

        // Update request currently has authorize() === false so expect 403
        $response->assertStatus(403);
    }

    public function test_destroy_deletes_treatment(): void
    {
        $treatment = AiRiskTreatment::factory()->create(['organization_id' => $this->organization->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/ai-risk-treatments/{$treatment->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('ai_risk_treatments', ['id' => $treatment->id]);
    }

    public function test_unauthenticated_cannot_access_endpoints(): void
    {
        $treatment = AiRiskTreatment::factory()->create();

        $this->getJson('/api/ai-risk-treatments')->assertUnauthorized();
        $this->postJson('/api/ai-risk-treatments', [])->assertUnauthorized();
        $this->getJson("/api/ai-risk-treatments/{$treatment->id}")->assertUnauthorized();
        $this->postJson("/api/ai-risk-treatments/{$treatment->id}", [])->assertUnauthorized();
        $this->deleteJson("/api/ai-risk-treatments/{$treatment->id}")->assertUnauthorized();
    }
}

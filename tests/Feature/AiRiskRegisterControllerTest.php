<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AiModel;
use App\Models\Stakeholder;
use App\Models\Organization;
use App\Models\AiRiskRegister;
use App\Models\RiskMethodology;
use App\Enums\AiRiskRegister\RiskLevel;
use App\Enums\AiRiskRegister\RiskStatus;
use App\Enums\AiRiskRegister\RiskCategory;
use App\Enums\AiRiskRegister\RiskDecision;
use App\Enums\AiRiskRegister\ReviewCadence;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiRiskRegisterControllerTest extends TestCase
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

    public function test_index_returns_paginated_ai_risk_register_entries(): void
    {
        AiRiskRegister::factory()->count(15)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-risk-register');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'risk_methodology_id',
                            'organization_id',
                            'title',
                            'risk_category',
                            'ai_model_id',
                            'description',
                            'risk_level',
                            'status',
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
        AiRiskRegister::factory()->count(20)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-risk-register');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 15);
    }

    public function test_index_accepts_custom_per_page(): void
    {
        AiRiskRegister::factory()->count(20)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-risk-register?per_page=5');

        $response->assertStatus(200)
            ->assertJsonPath('data.per_page', 5)
            ->assertJsonCount(5, 'data.data');
    }

    public function test_index_orders_by_created_at_desc(): void
    {
        $first = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'First Risk',
            'created_at' => now()->subDays(2),
        ]);
        $second = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Second Risk',
            'created_at' => now()->subDay(),
        ]);
        $third = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Third Risk',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/ai-risk-register');

        $response->assertStatus(200)
            ->assertJsonPath('data.data.0.title', 'Third Risk')
            ->assertJsonPath('data.data.1.title', 'Second Risk')
            ->assertJsonPath('data.data.2.title', 'First Risk');
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/ai-risk-register');

        $response->assertStatus(401);
    }

    public function test_store_creates_new_ai_risk_register_entry(): void
    {
        $aiModel = AiModel::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $stakeholder = Stakeholder::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'risk_methodology_id' => RiskMethodology::factory()->create()->id,
            'title' => 'Model Bias Risk',
            'risk_category' => RiskCategory::BIAS_FAIRNESS->value,
            'ai_model_id' => $aiModel->id,
            'description' => 'Potential bias in model predictions',
            'likelihood_code' => 'L3',
            'impact_code' => 'I4',
            'inherent_score' => '7',
            'residual_score' => '5',
            'risk_level' => RiskLevel::HIGH->value,
            'decision' => RiskDecision::TREAT->value,
            'risk_owner' => $stakeholder->id,
            'review_cadence' => ReviewCadence::QUARTERLY->value,
            'next_review_due' => now()->addMonths(3)->toDateString(),
            'status' => RiskStatus::ASSESSED->value,
            'likelihood_label_snapshot' => 'Possible',
            'impact_label_snapshot' => 'Major',
            'method_name_snapshot' => '5x5 Matrix',
            'created_by' => fake()->safeEmail(),
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-risk-register', $data);

        $response->assertStatus(201)
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'AI risk register entry created successfully')
            ->assertJsonPath('data.title', 'Model Bias Risk')
            ->assertJsonPath('data.risk_category', RiskCategory::BIAS_FAIRNESS->value)
            ->assertJsonPath('data.risk_level', RiskLevel::HIGH->value);

        $this->assertDatabaseHas('ai_risk_registers', [
            'title' => 'Model Bias Risk',
            'risk_category' => RiskCategory::BIAS_FAIRNESS->value,
            'organization_id' => $this->organization->id,
            'ai_model_id' => $aiModel->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-risk-register', []);

        $response->assertStatus(422);
    }

    public function test_store_validates_enum_values(): void
    {
        $aiModel = AiModel::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $stakeholder = Stakeholder::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'title' => 'Test Risk',
            'risk_category' => 'invalid_category',
            'ai_model_id' => $aiModel->id,
            'description' => 'Test description',
            'method_id' => 1,
            'likelihood_code' => 'L3',
            'impact_code' => 'I3',
            'risk_level' => 'invalid_level',
            'decision' => 'invalid_decision',
            'risk_owner' => $stakeholder->id,
            'review_cadence' => 'invalid_cadence',
            'next_review_due' => now()->addMonths(3)->toDateString(),
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson('/api/ai-risk-register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'risk_category',
                'risk_level',
                'decision',
                'review_cadence',
                'status',
            ]);
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/ai-risk-register', []);

        $response->assertStatus(401);
    }

    public function test_show_returns_ai_risk_register_entry(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Privacy Risk',
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/ai-risk-register/{$aiRiskRegister->id}");

        $response->assertStatus(200)
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'AI risk register entry retrieved successfully')
            ->assertJsonPath('data.id', $aiRiskRegister->id)
            ->assertJsonPath('data.title', 'Privacy Risk');
    }

    public function test_show_loads_relationships(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/ai-risk-register/{$aiRiskRegister->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'ai_model',
                    'risk_owner_details',
                ],
            ]);
    }

    public function test_show_requires_authentication(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->getJson("/api/ai-risk-register/{$aiRiskRegister->id}");

        $response->assertStatus(401);
    }

    public function test_update_modifies_ai_risk_register_entry(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Original Title',
            'risk_level' => RiskLevel::MEDIUM->value,
            'status' => RiskStatus::ASSESSED->value,
        ]);

        $data = [
            'title' => 'Updated Title',
            'risk_level' => RiskLevel::HIGH->value,
            'status' => RiskStatus::IN_TREATMENT->value,
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-risk-register/{$aiRiskRegister->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'AI risk register entry updated successfully')
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.risk_level', RiskLevel::HIGH->value)
            ->assertJsonPath('data.status', RiskStatus::IN_TREATMENT->value);

        $this->assertDatabaseHas('ai_risk_registers', [
            'id' => $aiRiskRegister->id,
            'title' => 'Updated Title',
            'risk_level' => RiskLevel::HIGH->value,
            'status' => RiskStatus::IN_TREATMENT->value,
        ]);
    }

    public function test_update_validates_enum_values(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $data = [
            'risk_level' => 'invalid_level',
            'status' => 'invalid_status',
        ];

        $response = $this->actingAs($this->user, 'supabase')
            ->postJson("/api/ai-risk-register/{$aiRiskRegister->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'risk_level',
                'status',
            ]);
    }

    public function test_update_requires_authentication(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->postJson("/api/ai-risk-register/{$aiRiskRegister->id}", []);

        $response->assertStatus(401);
    }

    public function test_destroy_deletes_ai_risk_register_entry(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->deleteJson("/api/ai-risk-register/{$aiRiskRegister->id}");

        $response->assertStatus(200)
            ->assertJsonPath('error', false)
            ->assertJsonPath('message', 'AI risk register entry deleted successfully')
            ->assertJsonPath('data', null);

        $this->assertDatabaseMissing('ai_risk_registers', [
            'id' => $aiRiskRegister->id,
        ]);
    }

    public function test_destroy_requires_authentication(): void
    {
        $aiRiskRegister = AiRiskRegister::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->deleteJson("/api/ai-risk-register/{$aiRiskRegister->id}");

        $response->assertStatus(401);
    }
}

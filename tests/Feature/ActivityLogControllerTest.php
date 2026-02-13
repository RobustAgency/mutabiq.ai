<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Organization;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityLogControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Organization $organization;

    protected Organization $otherOrganization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->otherOrganization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_index_returns_paginated_activity_logs(): void
    {
        ActivityLog::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/activity-logs');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'message' => 'Activity logs retrieved successfully',
            ])
            ->assertJsonStructure([
                'error',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'organization_id',
                            'user_id',
                            'actable_type',
                            'actable_id',
                            'action',
                            'description',
                            'changes',
                            'ip_address',
                            'user_agent',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_user_can_only_view_logs_for_their_organization(): void
    {
        // Create logs for user's organization
        ActivityLog::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
        ]);

        // Create logs for other organization
        ActivityLog::factory()->count(3)->create([
            'organization_id' => $this->otherOrganization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/activity-logs');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
    }

    public function test_filter_by_user_id(): void
    {
        $otherUser = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        ActivityLog::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
        ]);

        ActivityLog::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/activity-logs?user_id={$this->user->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_filter_by_action(): void
    {
        ActivityLog::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'action' => ActivityAction::CREATE->value,
        ]);

        ActivityLog::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'action' => ActivityAction::UPDATE->value,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/activity-logs?action='.ActivityAction::CREATE->value);

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_filter_by_actable_type(): void
    {
        ActivityLog::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'actable_type' => 'App\Models\AiIncident',
        ]);

        ActivityLog::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'actable_type' => 'App\Models\AiModel',
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/activity-logs?actable_type=App%5CModels%5CAiIncident');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_filter_by_date_range(): void
    {
        ActivityLog::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'created_at' => now()->subDays(5),
        ]);

        ActivityLog::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'created_at' => now(),
        ]);

        $from = now()->subDays(2)->format('Y-m-d');
        $to = now()->format('Y-m-d');

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson("/api/activity-logs?from={$from}&to={$to}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_pagination_works_correctly(): void
    {
        ActivityLog::factory()->count(25)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user, 'supabase')
            ->getJson('/api/activity-logs?per_page=10');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'per_page' => 10,
                    'total' => 25,
                ],
            ]);

        $this->assertCount(10, $response->json('data.data'));
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/activity-logs');

        $response->assertStatus(401);
    }
}

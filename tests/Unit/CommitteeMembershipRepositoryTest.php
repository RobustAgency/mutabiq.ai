<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AiCommittee;
use App\Models\Stakeholder;
use App\Models\CommitteeMembership;
use App\Enums\CommitteeMembership\MemberRole;
use App\Enums\CommitteeMembership\Eligibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\CommitteeMembershipRepository;

class CommitteeMembershipRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CommitteeMembershipRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(CommitteeMembershipRepository::class);
    }

    /**
     * Test getting filtered memberships with default pagination
     */
    public function test_get_filtered_memberships_with_default_pagination(): void
    {
        CommitteeMembership::factory(20)->create();

        $result = $this->repository->getFilteredCommitteeMemberships([]);

        $this->assertEquals(15, $result->count());
        $this->assertEquals(20, $result->total());
    }

    /**
     * Test getting filtered memberships with custom per_page
     */
    public function test_get_filtered_memberships_with_custom_per_page(): void
    {
        CommitteeMembership::factory(25)->create();

        $result = $this->repository->getFilteredCommitteeMemberships(['per_page' => 10]);

        $this->assertEquals(10, $result->count());
        $this->assertEquals(25, $result->total());
    }

    /**
     * Test filtering by ai_committee_id
     */
    public function test_filter_by_ai_committee_id(): void
    {
        $committee1 = AiCommittee::factory()->create();
        $committee2 = AiCommittee::factory()->create();

        CommitteeMembership::factory(5)->create(['ai_committee_id' => $committee1->id]);
        CommitteeMembership::factory(3)->create(['ai_committee_id' => $committee2->id]);

        $result = $this->repository->getFilteredCommitteeMemberships([
            'ai_committee_id' => $committee1->id,
        ]);

        $this->assertEquals(5, $result->total());
        $this->assertTrue($result->every(fn ($m) => $m->ai_committee_id === $committee1->id));
    }

    /**
     * Test filtering by stakeholder_id
     */
    public function test_filter_by_stakeholder_id(): void
    {
        $stakeholder1 = Stakeholder::factory()->create();
        $stakeholder2 = Stakeholder::factory()->create();

        CommitteeMembership::factory(4)->create(['stakeholder_id' => $stakeholder1->id]);
        CommitteeMembership::factory(6)->create(['stakeholder_id' => $stakeholder2->id]);

        $result = $this->repository->getFilteredCommitteeMemberships([
            'stakeholder_id' => $stakeholder1->id,
        ]);

        $this->assertEquals(4, $result->total());
        $this->assertTrue($result->every(fn ($m) => $m->stakeholder_id === $stakeholder1->id));
    }

    /**
     * Test filtering by member_role
     */
    public function test_filter_by_member_role(): void
    {
        CommitteeMembership::factory(5)->create(['member_role' => MemberRole::CHAIR->value]);
        CommitteeMembership::factory(3)->create(['member_role' => MemberRole::ADVISOR->value]);

        $result = $this->repository->getFilteredCommitteeMemberships([
            'member_role' => MemberRole::CHAIR->value,
        ]);

        $this->assertEquals(5, $result->total());
        $this->assertTrue($result->every(fn ($m) => $m->member_role === MemberRole::CHAIR->value));
    }

    /**
     * Test filtering by eligibility
     */
    public function test_filter_by_eligibility(): void
    {
        CommitteeMembership::factory(4)->create(['eligibility' => Eligibility::ACTIVE->value]);
        CommitteeMembership::factory(6)->create(['eligibility' => Eligibility::SUSPENDED->value]);

        $result = $this->repository->getFilteredCommitteeMemberships([
            'eligibility' => Eligibility::ACTIVE->value,
        ]);

        $this->assertEquals(4, $result->total());
        $this->assertTrue($result->every(fn ($m) => $m->eligibility === Eligibility::ACTIVE->value));
    }

    /**
     * Test filtering by active (no end_date)
     */
    public function test_filter_by_active(): void
    {
        CommitteeMembership::factory(7)->create(['end_date' => null]);
        CommitteeMembership::factory(5)->create(['end_date' => now()->subMonth()]);

        $result = $this->repository->getFilteredCommitteeMemberships([
            'active' => true,
        ]);

        $this->assertEquals(7, $result->total());
        $this->assertTrue($result->every(fn ($m) => $m->end_date === null));
    }

    /**
     * Test filtering by multiple filters combined
     */
    public function test_filter_by_multiple_filters(): void
    {
        $committee = AiCommittee::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        CommitteeMembership::factory(10)->create([
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::ACTIVE->value,
            'end_date' => null,
        ]);

        CommitteeMembership::factory(5)->create([
            'ai_committee_id' => $committee->id,
            'member_role' => MemberRole::ADVISOR->value,
        ]);

        $result = $this->repository->getFilteredCommitteeMemberships([
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::ACTIVE->value,
            'active' => true,
        ]);

        $this->assertEquals(10, $result->total());
    }

    /**
     * Test filtering with no matching results
     */
    public function test_filter_with_no_matching_results(): void
    {
        $committee = AiCommittee::factory()->create();
        CommitteeMembership::factory(5)->create(['ai_committee_id' => $committee->id]);

        $result = $this->repository->getFilteredCommitteeMemberships([
            'ai_committee_id' => 99999,
        ]);

        $this->assertEquals(0, $result->total());
    }

    /**
     * Test filter with empty filters array
     */
    public function test_filter_with_empty_filters_array(): void
    {
        CommitteeMembership::factory(10)->create();

        $result = $this->repository->getFilteredCommitteeMemberships([]);

        $this->assertEquals(10, $result->total());
    }

    /**
     * Test create membership
     */
    public function test_create_membership(): void
    {
        $committee = AiCommittee::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::ACTIVE->value,
            'start_date' => now(),
            'end_date' => null,
            'expertise_tags' => ['governance', 'ethics'],
        ];

        $membership = $this->repository->createCommitteeMembership($data);

        $this->assertInstanceOf(CommitteeMembership::class, $membership);
        $this->assertEquals($committee->id, $membership->ai_committee_id);
        $this->assertEquals($stakeholder->id, $membership->stakeholder_id);
        $this->assertEquals(MemberRole::CHAIR->value, $membership->member_role);
        $this->assertDatabaseHas('committee_memberships', ['id' => $membership->id]);
    }

    /**
     * Test create membership with minimal data
     */
    public function test_create_membership_with_minimal_data(): void
    {
        $committee = AiCommittee::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        $data = [
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
            'member_role' => MemberRole::ADVISOR->value,
            'eligibility' => Eligibility::SUSPENDED->value,
            'start_date' => now(),
        ];

        $membership = $this->repository->createCommitteeMembership($data);

        $this->assertNotNull($membership->id);
        $this->assertNull($membership->end_date);
    }

    /**
     * Test create membership returns instance with id
     */
    public function test_create_membership_returns_instance_with_id(): void
    {
        $data = CommitteeMembership::factory()->make()->toArray();
        $data['ai_committee_id'] = AiCommittee::factory()->create()->id;
        $data['stakeholder_id'] = Stakeholder::factory()->create()->id;

        $membership = $this->repository->createCommitteeMembership($data);

        $this->assertNotNull($membership->id);
        $this->assertTrue($membership->id > 0);
    }

    /**
     * Test create multiple memberships
     */
    public function test_create_multiple_memberships(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $data = CommitteeMembership::factory()->make()->toArray();
            $data['ai_committee_id'] = AiCommittee::factory()->create()->id;
            $data['stakeholder_id'] = Stakeholder::factory()->create()->id;

            $this->repository->createCommitteeMembership($data);
        }

        $this->assertDatabaseCount('committee_memberships', 3);
    }

    /**
     * Test update membership with full data
     */
    public function test_update_membership_full(): void
    {
        $membership = CommitteeMembership::factory()->create([
            'member_role' => MemberRole::ADVISOR->value,
            'eligibility' => Eligibility::ACTIVE->value,
        ]);

        $updateData = [
            'member_role' => MemberRole::CHAIR->value,
            'eligibility' => Eligibility::SUSPENDED->value,
            'end_date' => now(),
        ];

        $updated = $this->repository->updateCommitteeMembership($membership, $updateData);

        $this->assertEquals(MemberRole::CHAIR->value, $updated->member_role);
        $this->assertEquals(Eligibility::SUSPENDED->value, $updated->eligibility);
        $this->assertNotNull($updated->end_date);
    }

    /**
     * Test update membership with partial data
     */
    public function test_update_membership_partial(): void
    {
        $originalRole = MemberRole::ADVISOR->value;
        $membership = CommitteeMembership::factory()->create([
            'member_role' => $originalRole,
            'eligibility' => Eligibility::ACTIVE->value,
        ]);

        $updateData = [
            'eligibility' => Eligibility::SUSPENDED->value,
        ];

        $updated = $this->repository->updateCommitteeMembership($membership, $updateData);

        $this->assertEquals($originalRole, $updated->member_role);
        $this->assertEquals(Eligibility::SUSPENDED->value, $updated->eligibility);
    }

    /**
     * Test update membership returns fresh instance
     */
    public function test_update_returns_fresh_instance(): void
    {
        $membership = CommitteeMembership::factory()->create([
            'eligibility' => Eligibility::ACTIVE->value,
        ]);

        $updated = $this->repository->updateCommitteeMembership($membership, [
            'eligibility' => Eligibility::SUSPENDED->value,
        ]);

        $this->assertNotSame($membership, $updated);
        $this->assertEquals(Eligibility::SUSPENDED->value, $updated->eligibility);
    }

    /**
     * Test update membership persists to database
     */
    public function test_update_membership_persists_to_database(): void
    {
        $membership = CommitteeMembership::factory()->create([
            'member_role' => MemberRole::ADVISOR->value,
        ]);

        $this->repository->updateCommitteeMembership($membership, [
            'member_role' => MemberRole::CHAIR->value,
        ]);

        $this->assertDatabaseHas('committee_memberships', [
            'id' => $membership->id,
            'member_role' => MemberRole::CHAIR->value,
        ]);
    }

    /**
     * Test update preserves unmodified fields
     */
    public function test_update_preserves_unmodified_fields(): void
    {
        $originalEligibility = Eligibility::ACTIVE->value;
        $membership = CommitteeMembership::factory()->create([
            'member_role' => MemberRole::ADVISOR->value,
            'eligibility' => $originalEligibility,
        ]);

        $updated = $this->repository->updateCommitteeMembership($membership, [
            'member_role' => MemberRole::CHAIR->value,
        ]);

        $this->assertEquals($originalEligibility, $updated->eligibility);
    }

    /**
     * Test update with all member_role enum values
     */
    public function test_update_with_all_member_role_values(): void
    {
        $membership = CommitteeMembership::factory()->create();

        foreach (MemberRole::cases() as $role) {
            $updated = $this->repository->updateCommitteeMembership($membership, [
                'member_role' => $role->value,
            ]);

            $this->assertEquals($role->value, $updated->member_role);
        }
    }

    /**
     * Test update with all eligibility enum values
     */
    public function test_update_with_all_eligibility_values(): void
    {
        $membership = CommitteeMembership::factory()->create();

        foreach (Eligibility::cases() as $eligibility) {
            $updated = $this->repository->updateCommitteeMembership($membership, [
                'eligibility' => $eligibility->value,
            ]);

            $this->assertEquals($eligibility->value, $updated->eligibility);
        }
    }

    /**
     * Test delete membership
     */
    public function test_delete_membership(): void
    {
        $membership = CommitteeMembership::factory()->create();
        $id = $membership->id;

        $result = $this->repository->deleteCommitteeMembership($membership);

        $this->assertTrue($result);
        $this->assertNull(CommitteeMembership::find($id));
    }

    /**
     * Test delete returns true for successful deletion
     */
    public function test_delete_returns_true_on_success(): void
    {
        $membership = CommitteeMembership::factory()->create();

        $result = $this->repository->deleteCommitteeMembership($membership);

        $this->assertTrue($result);
        $this->assertIsBool($result);
    }

    /**
     * Test delete non-existent membership returns false
     */
    public function test_delete_returns_false_for_already_deleted(): void
    {
        $membership = CommitteeMembership::factory()->create();
        $membership->delete();

        $result = $this->repository->deleteCommitteeMembership($membership);

        $this->assertFalse($result);
    }

    /**
     * Test filter returns paginator instance
     */
    public function test_filter_returns_paginator_instance(): void
    {
        CommitteeMembership::factory(5)->create();

        $result = $this->repository->getFilteredCommitteeMemberships([]);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
    }

    /**
     * Test filter pagination data structure
     */
    public function test_filter_pagination_data_structure(): void
    {
        CommitteeMembership::factory(25)->create();

        $result = $this->repository->getFilteredCommitteeMemberships(['per_page' => 10]);

        $this->assertNotNull($result->currentPage());
        $this->assertNotNull($result->perPage());
        $this->assertNotNull($result->total());
        $this->assertTrue($result->hasPages());
    }

    /**
     * Test filter with committee and stakeholder combination
     */
    public function test_filter_by_committee_and_stakeholder_combination(): void
    {
        $committee = AiCommittee::factory()->create();
        $stakeholder = Stakeholder::factory()->create();

        CommitteeMembership::factory()->create([
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
        ]);

        CommitteeMembership::factory()->create([
            'ai_committee_id' => $committee->id,
        ]);

        CommitteeMembership::factory()->create([
            'stakeholder_id' => $stakeholder->id,
        ]);

        $result = $this->repository->getFilteredCommitteeMemberships([
            'ai_committee_id' => $committee->id,
            'stakeholder_id' => $stakeholder->id,
        ]);

        $this->assertEquals(1, $result->total());
    }
}

<?php

namespace Tests\Feature\Repositories;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\SubjectRealm;
use App\Models\ConsentScope;
use App\Models\Dataset;
use App\Models\Organization;
use App\Repositories\ConsentScopeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentScopeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ConsentScopeRepository $repository;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ConsentScopeRepository();
        $this->organization = Organization::factory()->create();
    }

    /**
     * Test get paginated consent scopes returns correct structure.
     */
    public function test_get_paginated_consent_scopes_returns_paginator(): void
    {
        ConsentScope::factory()->count(5)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getFilteredConsentScopes(['organization_id' => $this->organization->id]);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->total());
    }

    /**
     * Test get paginated consent scopes eager loads dataset relationship.
     */
    public function test_get_paginated_consent_scopes_eager_loads_dataset(): void
    {
        $dataset = Dataset::factory()->create(['organization_id' => $this->organization->id]);
        ConsentScope::factory()->for($dataset)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getFilteredConsentScopes(['organization_id' => $this->organization->id]);

        /** @var ConsentScope $consentScope */
        $consentScope = $result->items()[0];
        $this->assertTrue($consentScope->relationLoaded('dataset'));
        $this->assertEquals($dataset->id, $consentScope->dataset->id);
    }

    /**
     * Test get paginated consent scopes respects per page parameter.
     */
    public function test_get_paginated_consent_scopes_respects_per_page(): void
    {
        ConsentScope::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getFilteredConsentScopes([
            'organization_id' => $this->organization->id,
            'per_page' => 10,
        ]);

        $this->assertEquals(10, $result->perPage());
        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }

    /**
     * Test get paginated consent scopes with default per page.
     */
    public function test_get_paginated_consent_scopes_uses_default_per_page(): void
    {
        ConsentScope::factory()->count(20)->create(['organization_id' => $this->organization->id]);

        $result = $this->repository->getFilteredConsentScopes([
            'organization_id' => $this->organization->id,
        ]);

        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test get paginated consent scopes orders by created_at desc.
     */
    public function test_get_paginated_consent_scopes_ordered_by_created_at_desc(): void
    {
        $scope1 = ConsentScope::factory()->create([
            'created_at' => now()->subDays(3),
            'organization_id' => $this->organization->id,
        ]);
        $scope2 = ConsentScope::factory()->create([
            'created_at' => now()->subDays(1),
            'organization_id' => $this->organization->id,
        ]);
        $scope3 = ConsentScope::factory()->create([
            'created_at' => now()->subDays(2),
            'organization_id' => $this->organization->id,
        ]);

        $result = $this->repository->getFilteredConsentScopes([
            'organization_id' => $this->organization->id,
            'per_page' => 10,
        ]);

        $this->assertEquals($scope2->id, $result->items()[0]->id);
        $this->assertEquals($scope3->id, $result->items()[1]->id);
        $this->assertEquals($scope1->id, $result->items()[2]->id);
    }

    /**
     * Test create consent scope creates a new consent scope.
     */
    public function test_create_consent_scope_creates_new_consent_scope(): void
    {
        $organization = Organization::factory()->create();
        $dataset = Dataset::factory()->create(['organization_id' => $organization->id]);

        $data = [
            'organization_id' => $organization->id,
            'dataset_id' => $dataset->id,
            'purpose' => [ConsentPurpose::MARKETING->value, ConsentPurpose::ANALYTICS->value],
            'subject_realm' => SubjectRealm::CUSTOMER,
            'jurisdiction' => Jurisdiction::EU,
            'effective_from' => now(),
            'effective_to' => now()->addYear(),
        ];

        $result = $this->repository->createConsentScope($data);

        $this->assertInstanceOf(ConsentScope::class, $result);
        $this->assertEquals($data['dataset_id'], $result->dataset_id);
        $this->assertEquals($data['purpose'], $result->purpose);
        $this->assertEquals($data['subject_realm'], $result->subject_realm);
        $this->assertEquals($data['jurisdiction'], $result->jurisdiction);
        $this->assertDatabaseHas('consent_scopes', [
            'dataset_id' => $dataset->id,
            'purpose' => json_encode([ConsentPurpose::MARKETING->value, ConsentPurpose::ANALYTICS->value]),
        ]);
    }

    /**
     * Test create consent scope with minimal required data.
     */
    public function test_create_consent_scope_with_minimal_data(): void
    {
        $organization = Organization::factory()->create();
        $dataset = Dataset::factory()->create(['organization_id' => $organization->id]);

        $data = [
            'organization_id' => $organization->id,
            'dataset_id' => $dataset->id,
            'purpose' => [ConsentPurpose::ANALYTICS->value],
            'subject_realm' => SubjectRealm::PROSPECT,
            'jurisdiction' => Jurisdiction::US,
            'effective_from' => now(),
        ];

        $result = $this->repository->createConsentScope($data);

        $this->assertInstanceOf(ConsentScope::class, $result);
        $this->assertNull($result->effective_to);
    }

    /**
     * Test update consent scope updates existing consent scope.
     */
    public function test_update_consent_scope_updates_existing_consent_scope(): void
    {
        $consentScope = ConsentScope::factory()->create([
            'purpose' => [ConsentPurpose::MARKETING->value],
            'jurisdiction' => Jurisdiction::EU,
        ]);

        $updateData = [
            'purpose' => [ConsentPurpose::TRAINING_AI->value],
            'jurisdiction' => Jurisdiction::US,
        ];

        $result = $this->repository->updateConsentScope($consentScope, $updateData);

        $this->assertTrue($result);
        $consentScope->refresh();
        $this->assertEquals($updateData['purpose'], $consentScope->purpose);
        $this->assertEquals('US', $consentScope->jurisdiction);
    }

    /**
     * Test delete consent scope deletes the consent scope.
     */
    public function test_delete_consent_scope_deletes_consent_scope(): void
    {
        $consentScope = ConsentScope::factory()->create();
        $consentScopeId = $consentScope->id;

        $result = $this->repository->deleteConsentScope($consentScope);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('consent_scopes', ['id' => $consentScopeId]);
    }

    /**
     * Test delete consent scope returns false on failure.
     */
    public function test_delete_consent_scope_returns_false_on_failure(): void
    {
        $consentScope = ConsentScope::factory()->create();

        // Delete it first
        $consentScope->delete();

        // Try to delete again - should return false
        $result = $this->repository->deleteConsentScope($consentScope);

        $this->assertFalse($result);
    }

    /**
     * Test update consent scope can modify effective dates.
     */
    public function test_update_consent_scope_can_modify_effective_dates(): void
    {
        $consentScope = ConsentScope::factory()->create([
            'effective_from' => now()->subDays(10),
            'effective_to' => null,
        ]);

        $newEffectiveTo = now()->addMonths(6);
        $this->repository->updateConsentScope($consentScope, [
            'effective_to' => $newEffectiveTo,
        ]);

        $consentScope->refresh();
        $this->assertNotNull($consentScope->effective_to);
        $this->assertEquals($newEffectiveTo->format('Y-m-d'), $consentScope->effective_to->format('Y-m-d'));
    }

    /**
     * Test repository handles all consent purposes.
     */
    public function test_repository_handles_all_consent_purposes(): void
    {
        $purposes = [
            ConsentPurpose::MARKETING,
            ConsentPurpose::ANALYTICS,
            ConsentPurpose::PERSONALIZATION,
            ConsentPurpose::TRAINING_AI,
        ];

        foreach ($purposes as $purpose) {
            $consentScope = ConsentScope::factory()->create(['purpose' => [$purpose->value]]);
            $this->assertEquals([$purpose->value], $consentScope->purpose);
        }
    }

    /**
     * Test repository handles all jurisdictions.
     */
    public function test_repository_handles_all_jurisdictions(): void
    {
        $jurisdictions = [
            Jurisdiction::AE,
            Jurisdiction::EU,
            Jurisdiction::KSA,
            Jurisdiction::US,
            Jurisdiction::UK,
        ];

        foreach ($jurisdictions as $jurisdiction) {
            $consentScope = ConsentScope::factory()->create(['jurisdiction' => $jurisdiction]);
            $this->assertEquals($jurisdiction, $consentScope->jurisdiction);
        }
    }

    /**
     * Test repository handles all subject realms.
     */
    public function test_repository_handles_all_subject_realms(): void
    {
        $realms = [
            SubjectRealm::CUSTOMER,
            SubjectRealm::PROSPECT,
            SubjectRealm::EMPLOYEE,
            SubjectRealm::VENDOR,
            SubjectRealm::OTHER,
        ];

        foreach ($realms as $realm) {
            $consentScope = ConsentScope::factory()->create(['subject_realm' => $realm]);
            $this->assertEquals($realm, $consentScope->subject_realm);
        }
    }
}

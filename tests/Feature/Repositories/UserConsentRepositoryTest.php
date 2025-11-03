<?php

namespace Tests\Feature\Repositories;

use App\Enums\UserConsent\ConsentPurpose;
use App\Enums\UserConsent\ConsentStatus;
use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\LegalBasis;
use App\Enums\UserConsent\SubjectRealm;
use App\Models\UserConsent;
use App\Models\Organization;
use App\Repositories\UserConsentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserConsentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserConsentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserConsentRepository();
    }

    /**
     * Test get paginated consents returns correct structure.
     */
    public function test_get_paginated_consents_returns_paginator(): void
    {
        $organization = Organization::factory()->create();
        UserConsent::factory()->count(5)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getPaginatedConsents($organization->id);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->total());
    }

    /**
     * Test get paginated consents respects per page parameter.
     */
    public function test_get_paginated_consents_respects_per_page(): void
    {
        $organization = Organization::factory()->create();
        UserConsent::factory()->count(20)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getPaginatedConsents($organization->id, 10);

        $this->assertEquals(10, $result->perPage());
        $this->assertCount(10, $result->items());
        $this->assertEquals(20, $result->total());
    }

    /**
     * Test get paginated consents with default per page.
     */
    public function test_get_paginated_consents_uses_default_per_page(): void
    {
        $organization = Organization::factory()->create();
        UserConsent::factory()->count(20)->create(['organization_id' => $organization->id]);

        $result = $this->repository->getPaginatedConsents($organization->id);

        $this->assertEquals(15, $result->perPage());
    }

    /**
     * Test get paginated consents orders by created_at desc.
     */
    public function test_get_paginated_consents_ordered_by_created_at_desc(): void
    {
        $organization = Organization::factory()->create();
        $consent1 = UserConsent::factory()->create(['created_at' => now()->subDays(3), 'organization_id' => $organization->id]);
        $consent2 = UserConsent::factory()->create(['created_at' => now()->subDays(1), 'organization_id' => $organization->id]);
        $consent3 = UserConsent::factory()->create(['created_at' => now()->subDays(2), 'organization_id' => $organization->id]);

        $result = $this->repository->getPaginatedConsents($organization->id);

        $this->assertEquals($consent2->id, $result->items()[0]->id);
        $this->assertEquals($consent3->id, $result->items()[1]->id);
        $this->assertEquals($consent1->id, $result->items()[2]->id);
    }

    /**
     * Test get consent by ID returns consent.
     */
    public function test_get_consent_by_id_returns_consent(): void
    {
        $consent = UserConsent::factory()->create();

        $result = $this->repository->getConsentById($consent->id);

        $this->assertNotNull($result);
        $this->assertEquals($consent->id, $result->id);
        $this->assertEquals($consent->subject_key, $result->subject_key);
    }

    /**
     * Test get consent by ID returns null when not found.
     */
    public function test_get_consent_by_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->getConsentById(999999);

        $this->assertNull($result);
    }

    /**
     * Test create consent creates a new consent.
     */
    public function test_create_consent_creates_new_consent(): void
    {
        $organization = Organization::factory()->create();
        $data = [
            'organization_id' => $organization->id,
            'subject_key' => 'SUBJ-123456',
            'subject_realm' => SubjectRealm::CUSTOMER,
            'jurisdiction' => Jurisdiction::EU,
            'consent_purpose' => [ConsentPurpose::MARKETING->value, ConsentPurpose::ANALYTICS->value],
            'consent_status' => ConsentStatus::GRANTED,
            'legal_basis' => LegalBasis::CONSENT,
            'source_system' => 'Web Portal',
            'evidence_ref' => 'EVD-123456',
            'effective_from' => now(),
            'effective_to' => now()->addYear(),
            'scope' => 'Email marketing and website analytics',
        ];

        $result = $this->repository->createConsent($data);

        $this->assertInstanceOf(UserConsent::class, $result);
        $this->assertEquals($data['subject_key'], $result->subject_key);
        $this->assertEquals($data['subject_realm'], $result->subject_realm);
        $this->assertEquals($data['jurisdiction'], $result->jurisdiction);
        $this->assertEquals($data['consent_purpose'], $result->consent_purpose);
        $this->assertEquals($data['consent_status'], $result->consent_status);
        $this->assertDatabaseHas('user_consents', [
            'subject_key' => 'SUBJ-123456',
            'consent_status' => ConsentStatus::GRANTED->value,
        ]);
    }

    /**
     * Test create consent with minimal required data.
     */
    public function test_create_consent_with_minimal_data(): void
    {
        $organization = Organization::factory()->create();
        $data = [
            'organization_id' => $organization->id,
            'subject_key' => 'SUBJ-789',
            'subject_realm' => SubjectRealm::PROSPECT,
            'jurisdiction' => Jurisdiction::US,
            'consent_purpose' => [ConsentPurpose::SUPPORT->value],
            'consent_status' => ConsentStatus::GRANTED,
            'legal_basis' => LegalBasis::LEGITIMATE_INTERESTS,
            'source_system' => 'Mobile App',
            'evidence_ref' => 'EVD-789',
            'effective_from' => now(),
        ];

        $result = $this->repository->createConsent($data);

        $this->assertInstanceOf(UserConsent::class, $result);
        $this->assertNull($result->effective_to);
        $this->assertNull($result->scope);
    }

    /**
     * Test create consent with multiple purposes.
     */
    public function test_create_consent_with_multiple_purposes(): void
    {
        $organization = Organization::factory()->create();
        $purposes = [
            ConsentPurpose::MARKETING->value,
            ConsentPurpose::ANALYTICS->value,
            ConsentPurpose::PERSONALIZATION->value,
            ConsentPurpose::TRAINING_AI->value,
        ];

        $data = [
            'organization_id' => $organization->id,
            'subject_key' => 'SUBJ-MULTI',
            'subject_realm' => SubjectRealm::CUSTOMER,
            'jurisdiction' => Jurisdiction::EU,
            'consent_purpose' => $purposes,
            'consent_status' => ConsentStatus::GRANTED,
            'legal_basis' => LegalBasis::CONSENT,
            'source_system' => 'API',
            'evidence_ref' => 'EVD-MULTI',
            'effective_from' => now(),
        ];

        $result = $this->repository->createConsent($data);

        $this->assertCount(4, $result->consent_purpose);
        $this->assertEquals($purposes, $result->consent_purpose);
    }

    /**
     * Test update consent updates existing consent.
     */
    public function test_update_consent_updates_existing_consent(): void
    {
        $consent = UserConsent::factory()->create([
            'consent_status' => ConsentStatus::GRANTED,
            'subject_key' => 'SUBJ-OLD',
        ]);

        $updateData = [
            'subject_key' => 'SUBJ-NEW',
            'consent_status' => ConsentStatus::WITHDRAWN,
            'scope' => 'Updated scope',
        ];

        $result = $this->repository->updateConsent($consent, $updateData);

        $this->assertTrue($result);
        $consent->refresh();
        $this->assertEquals('SUBJ-NEW', $consent->subject_key);
        $this->assertEquals(ConsentStatus::WITHDRAWN->value, $consent->consent_status);
        $this->assertEquals('Updated scope', $consent->scope);
    }

    /**
     * Test update consent can change consent status.
     */
    public function test_update_consent_can_change_consent_status(): void
    {
        $consent = UserConsent::factory()->create([
            'consent_status' => ConsentStatus::GRANTED,
        ]);

        $this->repository->updateConsent($consent, [
            'consent_status' => ConsentStatus::WITHDRAWN->value,
        ]);

        $consent->refresh();
        $this->assertEquals(ConsentStatus::WITHDRAWN->value, $consent->consent_status);
    }

    /**
     * Test update consent can modify effective dates.
     */
    public function test_update_consent_can_modify_effective_dates(): void
    {
        $consent = UserConsent::factory()->create([
            'effective_from' => now()->subDays(10),
            'effective_to' => null,
        ]);

        $newEffectiveTo = now()->addMonths(6);
        $this->repository->updateConsent($consent, [
            'effective_to' => $newEffectiveTo,
        ]);

        $consent->refresh();
        $this->assertNotNull($consent->effective_to);
        $this->assertEquals($newEffectiveTo->format('Y-m-d'), $consent->effective_to->format('Y-m-d'));
    }

    /**
     * Test update consent can add or remove purposes.
     */
    public function test_update_consent_can_modify_purposes(): void
    {
        $consent = UserConsent::factory()->create([
            'consent_purpose' => [ConsentPurpose::MARKETING->value],
        ]);

        $newPurposes = [
            ConsentPurpose::MARKETING->value,
            ConsentPurpose::ANALYTICS->value,
        ];

        $this->repository->updateConsent($consent, [
            'consent_purpose' => $newPurposes,
        ]);

        $consent->refresh();
        $this->assertCount(2, $consent->consent_purpose);
        $this->assertEquals($newPurposes, $consent->consent_purpose);
    }

    /**
     * Test delete consent deletes the consent.
     */
    public function test_delete_consent_deletes_consent(): void
    {
        $consent = UserConsent::factory()->create();
        $consentId = $consent->id;

        $result = $this->repository->deleteConsent($consent);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('user_consents', ['id' => $consentId]);
    }

    /**
     * Test delete consent returns false on failure.
     */
    public function test_delete_consent_returns_false_on_failure(): void
    {
        $consent = UserConsent::factory()->create();

        // Delete it first
        $consent->delete();

        // Try to delete again - should return false
        $result = $this->repository->deleteConsent($consent);

        $this->assertFalse($result);
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
            $consent = UserConsent::factory()->create(['subject_realm' => $realm]);
            $this->assertEquals($realm, $consent->subject_realm);
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
            $consent = UserConsent::factory()->create(['jurisdiction' => $jurisdiction]);
            $this->assertEquals($jurisdiction, $consent->jurisdiction);
        }
    }

    /**
     * Test repository handles all consent statuses.
     */
    public function test_repository_handles_all_consent_statuses(): void
    {
        $statuses = [
            ConsentStatus::GRANTED,
            ConsentStatus::DENIED,
            ConsentStatus::WITHDRAWN,
            ConsentStatus::EXPIRED,
            ConsentStatus::NOT_OBTAINED,
        ];

        foreach ($statuses as $status) {
            $consent = UserConsent::factory()->create(['consent_status' => $status]);
            $this->assertEquals($status, $consent->consent_status);
        }
    }

    /**
     * Test repository handles all legal bases.
     */
    public function test_repository_handles_all_legal_bases(): void
    {
        $legalBases = [
            LegalBasis::CONSENT,
            LegalBasis::CONTRACT,
            LegalBasis::LEGAL_OBLIGATION,
            LegalBasis::LEGITIMATE_INTERESTS,
            LegalBasis::PUBLIC_TASK,
            LegalBasis::VITAL_INTERESTS,
        ];

        foreach ($legalBases as $legalBasis) {
            $consent = UserConsent::factory()->create(['legal_basis' => $legalBasis]);
            $this->assertEquals($legalBasis, $consent->legal_basis);
        }
    }

    /**
     * Test repository handles nullable effective_to.
     */
    public function test_repository_handles_nullable_effective_to(): void
    {
        $consent = UserConsent::factory()->create([
            'effective_to' => null,
        ]);

        $result = $this->repository->getConsentById($consent->id);

        $this->assertNull($result->effective_to);
    }

    /**
     * Test repository handles nullable scope.
     */
    public function test_repository_handles_nullable_scope(): void
    {
        $consent = UserConsent::factory()->create([
            'scope' => null,
        ]);

        $result = $this->repository->getConsentById($consent->id);

        $this->assertNull($result->scope);
    }
}

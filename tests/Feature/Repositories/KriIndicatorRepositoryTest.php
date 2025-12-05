<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\KriIndicator;
use App\Models\Organization;
use App\Models\AiRiskRegister;
use App\Enums\KriIndicator\Status;
use App\Enums\KriIndicator\Frequency;
use App\Enums\KriIndicator\AlertRouting;
use App\Enums\KriIndicator\ActionOnBreach;
use App\Enums\KriIndicator\Directionality;
use App\Enums\KriIndicator\CollectionMethod;
use App\Repositories\KriIndicatorRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KriIndicatorRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private KriIndicatorRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(KriIndicatorRepository::class);
    }

    /**
     * Test getting filtered KRI indicators by organization
     */
    public function test_get_filtered_kri_indicators_by_organization(): void
    {
        $org = Organization::factory()->create();
        $otherOrg = Organization::factory()->create();

        KriIndicator::factory()->count(5)->create(['organization_id' => $org->id]);
        KriIndicator::factory()->count(3)->create(['organization_id' => $otherOrg->id]);

        $result = $this->repository->getFilteredKriIndicators([
            'organization_id' => $org->id,
            'per_page' => 15,
        ]);

        $this->assertEquals(5, $result->total());
        $this->assertTrue(
            collect($result->items())->every(fn ($item) => $item->organization_id === $org->id)
        );
    }

    /**
     * Test filtering KRI indicators by name
     */
    public function test_get_filtered_kri_indicators_by_name(): void
    {
        $org = Organization::factory()->create();

        KriIndicator::factory()->create([
            'organization_id' => $org->id,
            'name' => 'Model Accuracy Indicator',
        ]);
        KriIndicator::factory()->create([
            'organization_id' => $org->id,
            'name' => 'Latency Performance Metric',
        ]);
        KriIndicator::factory()->create([
            'organization_id' => $org->id,
            'name' => 'Data Quality Score',
        ]);

        $result = $this->repository->getFilteredKriIndicators([
            'organization_id' => $org->id,
            'name' => 'Accuracy',
            'per_page' => 15,
        ]);

        $this->assertEquals(1, $result->total());
        $this->assertStringContainsString('Accuracy', $result->first()->name);
    }

    /**
     * Test filtering KRI indicators by status
     */
    public function test_get_filtered_kri_indicators_by_status(): void
    {
        $org = Organization::factory()->create();

        KriIndicator::factory()->count(3)->create([
            'organization_id' => $org->id,
            'status' => Status::ACTIVE->value,
        ]);
        KriIndicator::factory()->count(2)->create([
            'organization_id' => $org->id,
            'status' => Status::PAUSED->value,
        ]);
        KriIndicator::factory()->create([
            'organization_id' => $org->id,
            'status' => Status::DRAFT->value,
        ]);

        $result = $this->repository->getFilteredKriIndicators([
            'organization_id' => $org->id,
            'status' => Status::ACTIVE->value,
            'per_page' => 15,
        ]);

        $this->assertEquals(3, $result->total());
        $this->assertTrue(
            collect($result->items())->every(fn ($item) => $item->status === Status::ACTIVE->value)
        );
    }

    /**
     * Test filtering KRI indicators by frequency
     */
    public function test_get_filtered_kri_indicators_by_frequency(): void
    {
        $org = Organization::factory()->create();

        KriIndicator::factory()->count(2)->create([
            'organization_id' => $org->id,
            'frequency' => Frequency::HOURLY->value,
        ]);
        KriIndicator::factory()->count(3)->create([
            'organization_id' => $org->id,
            'frequency' => Frequency::DAILY->value,
        ]);

        $result = $this->repository->getFilteredKriIndicators([
            'organization_id' => $org->id,
            'frequency' => Frequency::HOURLY->value,
            'per_page' => 15,
        ]);

        $this->assertEquals(2, $result->total());
        $this->assertTrue(
            collect($result->items())->every(fn ($item) => $item->frequency === Frequency::HOURLY->value)
        );
    }

    /**
     * Test filtering KRI indicators by directionality
     */
    public function test_get_filtered_kri_indicators_by_directionality(): void
    {
        $org = Organization::factory()->create();

        KriIndicator::factory()->count(4)->create([
            'organization_id' => $org->id,
            'directionality' => Directionality::HIGHER_IS_RISKIER->value,
        ]);
        KriIndicator::factory()->count(2)->create([
            'organization_id' => $org->id,
            'directionality' => Directionality::LOWER_IS_RISKIER->value,
        ]);

        $result = $this->repository->getFilteredKriIndicators([
            'organization_id' => $org->id,
            'directionality' => Directionality::HIGHER_IS_RISKIER->value,
            'per_page' => 15,
        ]);

        $this->assertEquals(4, $result->total());
        $this->assertTrue(
            collect($result->items())->every(fn ($item) => $item->directionality === Directionality::HIGHER_IS_RISKIER->value)
        );
    }

    /**
     * Test filtering KRI indicators by collection method
     */
    public function test_get_filtered_kri_indicators_by_collection_method(): void
    {
        $org = Organization::factory()->create();

        KriIndicator::factory()->count(3)->create([
            'organization_id' => $org->id,
            'collection_method' => CollectionMethod::SCHEDULED_QUERY->value,
        ]);
        KriIndicator::factory()->count(2)->create([
            'organization_id' => $org->id,
            'collection_method' => CollectionMethod::MANUAL_ENTRY->value,
        ]);

        $result = $this->repository->getFilteredKriIndicators([
            'organization_id' => $org->id,
            'collection_method' => CollectionMethod::SCHEDULED_QUERY->value,
            'per_page' => 15,
        ]);

        $this->assertEquals(3, $result->total());
        $this->assertTrue(
            collect($result->items())->every(fn ($item) => $item->collection_method === CollectionMethod::SCHEDULED_QUERY->value)
        );
    }

    /**
     * Test filtering KRI indicators by action on breach
     */
    public function test_get_filtered_kri_indicators_by_action_on_breach(): void
    {
        $org = Organization::factory()->create();

        KriIndicator::factory()->count(2)->create([
            'organization_id' => $org->id,
            'action_on_breach' => ActionOnBreach::NOTIFY_ONLY->value,
        ]);
        KriIndicator::factory()->count(3)->create([
            'organization_id' => $org->id,
            'action_on_breach' => ActionOnBreach::OPEN_INCIDENT->value,
        ]);

        $result = $this->repository->getFilteredKriIndicators([
            'organization_id' => $org->id,
            'action_on_breach' => ActionOnBreach::NOTIFY_ONLY->value,
            'per_page' => 15,
        ]);

        $this->assertEquals(2, $result->total());
        $this->assertTrue(
            collect($result->items())->every(fn ($item) => $item->action_on_breach === ActionOnBreach::NOTIFY_ONLY->value)
        );
    }

    /**
     * Test filtering with multiple filters
     */
    public function test_get_filtered_kri_indicators_with_multiple_filters(): void
    {
        $org = Organization::factory()->create();

        KriIndicator::factory()->count(5)->create([
            'organization_id' => $org->id,
            'status' => Status::ACTIVE->value,
            'frequency' => Frequency::DAILY->value,
            'collection_method' => CollectionMethod::SCHEDULED_QUERY->value,
        ]);
        KriIndicator::factory()->count(3)->create([
            'organization_id' => $org->id,
            'status' => Status::ACTIVE->value,
            'frequency' => Frequency::HOURLY->value,
        ]);

        $result = $this->repository->getFilteredKriIndicators([
            'organization_id' => $org->id,
            'status' => Status::ACTIVE->value,
            'frequency' => Frequency::DAILY->value,
            'collection_method' => CollectionMethod::SCHEDULED_QUERY->value,
            'per_page' => 15,
        ]);

        $this->assertEquals(5, $result->total());
        $this->assertTrue(
            collect($result->items())->every(fn ($item) => $item->status === Status::ACTIVE->value &&
                $item->frequency === Frequency::DAILY->value &&
                $item->collection_method === CollectionMethod::SCHEDULED_QUERY->value
            )
        );
    }

    /**
     * Test pagination of KRI indicators
     */
    public function test_get_filtered_kri_indicators_pagination(): void
    {
        $org = Organization::factory()->create();

        KriIndicator::factory(25)->create(['organization_id' => $org->id]);

        $result = $this->repository->getFilteredKriIndicators([
            'organization_id' => $org->id,
            'per_page' => 10,
        ]);

        $this->assertEquals(25, $result->total());
        $this->assertCount(10, $result->items());
        $this->assertTrue($result->hasPages());
        $this->assertTrue($result->hasMorePages());
    }

    /**
     * Test default pagination per_page is 15
     */
    public function test_get_filtered_kri_indicators_default_per_page(): void
    {
        $org = Organization::factory()->create();

        KriIndicator::factory(20)->create(['organization_id' => $org->id]);

        $result = $this->repository->getFilteredKriIndicators([
            'organization_id' => $org->id,
        ]);

        $this->assertEquals(20, $result->total());
        $this->assertCount(15, $result->items());
    }

    /**
     * Test ordering by created_at descending
     */
    public function test_get_filtered_kri_indicators_ordered_by_created_at_desc(): void
    {
        $org = Organization::factory()->create();

        $first = KriIndicator::factory()->create(['organization_id' => $org->id]);
        sleep(1);
        $second = KriIndicator::factory()->create(['organization_id' => $org->id]);
        sleep(1);
        $third = KriIndicator::factory()->create(['organization_id' => $org->id]);

        $result = $this->repository->getFilteredKriIndicators([
            'organization_id' => $org->id,
            'per_page' => 15,
        ]);

        $items = $result->items();
        $this->assertEquals($third->id, $items[0]->id);
        $this->assertEquals($second->id, $items[1]->id);
        $this->assertEquals($first->id, $items[2]->id);
    }

    /**
     * Test creating a KRI indicator
     */
    public function test_create_kri_indicator(): void
    {
        $org = Organization::factory()->create();
        $aiRiskRegister = AiRiskRegister::factory()->create(['organization_id' => $org->id]);
        $user = User::factory()->create();

        $data = [
            'organization_id' => $org->id,
            'ai_risk_register_id' => $aiRiskRegister->id,
            'name' => 'Test KRI Indicator',
            'definition' => 'Test definition for KRI indicator',
            'directionality' => Directionality::HIGHER_IS_RISKIER->value,
            'unit' => 'percentage',
            'sample_window' => 'daily',
            'threshold_warning' => 75,
            'threshold_critical' => 90,
            'data_source' => 'database',
            'collection_method' => CollectionMethod::SCHEDULED_QUERY->value,
            'frequency' => Frequency::DAILY->value,
            'alert_routing' => [AlertRouting::RISK_TEAM->value],
            'action_on_breach' => ActionOnBreach::NOTIFY_ONLY->value,
            'status' => Status::ACTIVE->value,
            'owner_team' => 'Risk Team',
            'notes' => 'Test notes',
            'created_by' => $user->id,
        ];

        $indicator = $this->repository->createKriIndicator($data);

        $this->assertInstanceOf(KriIndicator::class, $indicator);
        $this->assertEquals($data['name'], $indicator->name);
        $this->assertEquals($data['organization_id'], $indicator->organization_id);
        $this->assertEquals($data['status'], $indicator->status);
        $this->assertDatabaseHas('kri_indicators', [
            'id' => $indicator->id,
            'name' => 'Test KRI Indicator',
            'organization_id' => $org->id,
            'status' => Status::ACTIVE->value,
        ]);
    }

    /**
     * Test updating a KRI indicator
     */
    public function test_update_kri_indicator(): void
    {
        $indicator = KriIndicator::factory()->create([
            'status' => Status::DRAFT->value,
            'threshold_warning' => 70,
        ]);

        $updated = $this->repository->updateKriIndicator($indicator, [
            'status' => Status::ACTIVE->value,
            'threshold_warning' => 80,
        ]);

        $this->assertInstanceOf(KriIndicator::class, $updated);
        $this->assertEquals(Status::ACTIVE->value, $updated->status);
        $this->assertEquals(80, $updated->threshold_warning);
        $this->assertDatabaseHas('kri_indicators', [
            'id' => $indicator->id,
            'status' => Status::ACTIVE->value,
            'threshold_warning' => 80,
        ]);
    }

    /**
     * Test updating multiple fields of a KRI indicator
     */
    public function test_update_kri_indicator_multiple_fields(): void
    {
        $indicator = KriIndicator::factory()->create();
        $user = User::factory()->create();

        $updated = $this->repository->updateKriIndicator($indicator, [
            'name' => 'Updated Name',
            'status' => Status::PAUSED->value,
            'threshold_critical' => 95,
            'notes' => 'Updated notes',
            'frequency' => Frequency::WEEKLY->value,
        ]);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals(Status::PAUSED->value, $updated->status);
        $this->assertEquals(95, $updated->threshold_critical);
        $this->assertEquals('Updated notes', $updated->notes);
        $this->assertEquals(Frequency::WEEKLY->value, $updated->frequency);
    }

    /**
     * Test deleting a KRI indicator
     */
    public function test_delete_kri_indicator(): void
    {
        $indicator = KriIndicator::factory()->create();
        $indicatorId = $indicator->id;

        $this->repository->deleteKriIndicator($indicator);

        $this->assertDatabaseMissing('kri_indicators', ['id' => $indicatorId]);
    }

    /**
     * Test that deletion doesn't affect other indicators
     */
    public function test_delete_kri_indicator_does_not_affect_others(): void
    {
        $org = Organization::factory()->create();
        $indicator1 = KriIndicator::factory()->create(['organization_id' => $org->id]);
        $indicator2 = KriIndicator::factory()->create(['organization_id' => $org->id]);
        $indicator3 = KriIndicator::factory()->create(['organization_id' => $org->id]);

        $this->repository->deleteKriIndicator($indicator2);

        $this->assertDatabaseHas('kri_indicators', ['id' => $indicator1->id]);
        $this->assertDatabaseMissing('kri_indicators', ['id' => $indicator2->id]);
        $this->assertDatabaseHas('kri_indicators', ['id' => $indicator3->id]);
    }

    /**
     * Test empty result when no filters match
     */
    public function test_get_filtered_kri_indicators_empty_result(): void
    {
        $org = Organization::factory()->create();
        KriIndicator::factory()->create(['organization_id' => $org->id]);

        $result = $this->repository->getFilteredKriIndicators([
            'organization_id' => $org->id,
            'status' => Status::RETIRED->value,
            'per_page' => 15,
        ]);

        $this->assertEquals(0, $result->total());
        $this->assertCount(0, $result->items());
    }

    /**
     * Test case-insensitive name search
     */
    public function test_get_filtered_kri_indicators_case_insensitive_name_search(): void
    {
        $org = Organization::factory()->create();

        KriIndicator::factory()->create([
            'organization_id' => $org->id,
            'name' => 'Model Accuracy Test',
        ]);

        $result = $this->repository->getFilteredKriIndicators([
            'organization_id' => $org->id,
            'name' => 'accuracy',
            'per_page' => 15,
        ]);

        $this->assertEquals(1, $result->total());
    }
}

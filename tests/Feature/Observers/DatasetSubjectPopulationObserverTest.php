<?php

namespace Tests\Feature\Observers;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\DatasetSubjectPopulation;
use App\Enums\ActivityLog\ActivityAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatasetSubjectPopulationObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_activity_on_dataset_subject_population_create(): void
    {
        $population = DatasetSubjectPopulation::factory()->create();

        $log = ActivityLog::where('actable_id', $population->id)
            ->where('actable_type', DatasetSubjectPopulation::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::CREATE, $log->action);
        $this->assertEquals('DatasetSubjectPopulation created', $log->description);
    }

    public function test_logs_activity_on_dataset_subject_population_update(): void
    {
        $population = DatasetSubjectPopulation::factory()->create(['subjects_total' => 1000]);

        ActivityLog::truncate();

        $population->update(['subjects_total' => 2000]);

        $log = ActivityLog::where('actable_id', $population->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::UPDATE, $log->action);
        $this->assertEquals('DatasetSubjectPopulation updated', $log->description);
        $this->assertArrayHasKey('subjects_total', $log->changes);
        $this->assertEquals(1000, $log->changes['subjects_total']['from']);
        $this->assertEquals(2000, $log->changes['subjects_total']['to']);
    }

    public function test_logs_activity_on_dataset_subject_population_delete(): void
    {
        $population = DatasetSubjectPopulation::factory()->create();
        $populationId = $population->id;

        $population->delete();

        $log = ActivityLog::where('actable_id', $populationId)
            ->where('action', ActivityAction::DELETE)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals(ActivityAction::DELETE, $log->action);
        $this->assertEquals('DatasetSubjectPopulation deleted', $log->description);
    }

    public function test_tracks_all_specified_fields(): void
    {
        $population = DatasetSubjectPopulation::factory()->create([
            'subject_realm' => 'CUSTOMERS',
            'jurisdiction' => 'US',
            'subjects_total' => 1000,
        ]);

        ActivityLog::truncate();

        $population->update([
            'subject_realm' => 'EMPLOYEES',
            'jurisdiction' => 'EU',
            'subjects_total' => 5000,
        ]);

        $log = ActivityLog::where('actable_id', $population->id)
            ->where('action', ActivityAction::UPDATE)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('subject_realm', $log->changes);
        $this->assertArrayHasKey('jurisdiction', $log->changes);
        $this->assertArrayHasKey('subjects_total', $log->changes);
    }

    public function test_logs_activity_with_organization_id(): void
    {
        $population = DatasetSubjectPopulation::factory()->create();

        $log = ActivityLog::where('actable_id', $population->id)
            ->where('actable_type', DatasetSubjectPopulation::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($population->organization_id, $log->organization_id);
    }

    public function test_logs_activity_with_ip_and_user_agent(): void
    {
        $population = DatasetSubjectPopulation::factory()->create();

        $log = ActivityLog::where('actable_id', $population->id)
            ->where('actable_type', DatasetSubjectPopulation::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }
}

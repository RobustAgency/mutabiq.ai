<?php

namespace Tests\Feature\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\Control;
use App\Repositories\ControlRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ControlRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private ControlRepository $controlRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controlRepository = app(ControlRepository::class);
    }

    public function test_it_filter_controls_by_name(): void
    {
        $user = User::factory()->create();

        Control::factory()->create([
            'user_id' => $user->id,
            'name' => 'AI System Monitoring Control',
        ]);

        Control::factory()->create([
            'user_id' => $user->id,
            'name' => 'Data Privacy Control',
        ]);

        $results = $this->controlRepository->getFilteredControls($user, ['name' => 'AI']);

        $this->assertCount(1, $results);
        $this->assertEquals('AI System Monitoring Control', $results->first()->name);
    }

    public function test_it_filter_controls_by_status(): void
    {
        $user = User::factory()->create();

        Control::factory()->create([
            'user_id' => $user->id,
            'name' => 'Active Control',
            'status' => 'Active',
        ]);

        Control::factory()->create([
            'user_id' => $user->id,
            'name' => 'Inactive Control',
            'status' => 'Inactive',
        ]);

        $results = $this->controlRepository->getFilteredControls($user, ['status' => 'Active']);

        $this->assertCount(1, $results);
        $this->assertEquals('Active Control', $results->first()->name);
    }

    public function test_it_filter_controls_by_testing_frequency(): void
    {
        $user = User::factory()->create();

        Control::factory()->create([
            'user_id' => $user->id,
            'name' => 'Monthly Control',
            'testing_frequency' => 'Monthly',
        ]);

        Control::factory()->create([
            'user_id' => $user->id,
            'name' => 'Quarterly Control',
            'testing_frequency' => 'Quarterly',
        ]);

        $results = $this->controlRepository->getFilteredControls($user, ['testing_frequency' => 'Monthly']);

        $this->assertCount(1, $results);
        $this->assertEquals('Monthly Control', $results->first()->name);
    }

    public function test_it_applies_pagination_correctly(): void
    {
        $user = User::factory()->create();

        Control::factory()->count(15)->create([
            'user_id' => $user->id,
        ]);

        $results = $this->controlRepository->getFilteredControls($user, ['per_page' => 5]);

        $this->assertCount(5, $results);
        $this->assertEquals(5, $results->perPage());
        $this->assertEquals(15, $results->total());
    }

    public function test_it_creates_control(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'New Security Control',
            'reference' => 'SEC-001',
            'objective' => 'Ensure system security',
            'testing_method' => 'Automated Testing',
            'testing_frequency' => 'Monthly',
            'evidence_expectations' => 'Test reports must be available',
            'applicability_criteria' => 'All systems',
            'status' => 'Active',
            'last_test_date' => now()->subDays(5),
            'next_test_due' => now()->addDays(25),
        ];

        $control = $this->controlRepository->createForAdmin($user, $data);

        $this->assertNotNull($control->id);
        $this->assertEquals('New Security Control', $control->name);
        $this->assertEquals('SEC-001', $control->reference);
        $this->assertEquals('Active', $control->status);
        $this->assertEquals($user->id, $control->user_id);
        $this->assertDatabaseHas('controls', [
            'id' => $control->id,
            'name' => 'New Security Control',
            'user_id' => $user->id,
        ]);
    }

    public function test_it_updates_control(): void
    {
        $user = User::factory()->create();

        $control = Control::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old Control Name',
            'status' => 'Inactive',
            'testing_frequency' => 'Quarterly',
        ]);

        $data = [
            'name' => 'Updated Control Name',
            'status' => 'Active',
            'testing_frequency' => 'Monthly',
            'objective' => 'Updated objective',
        ];

        $updatedControl = $this->controlRepository->update($control, $data);

        $this->assertEquals('Updated Control Name', $updatedControl->name);
        $this->assertEquals('Active', $updatedControl->status);
        $this->assertEquals('Monthly', $updatedControl->testing_frequency);
        $this->assertEquals('Updated objective', $updatedControl->objective);
        $this->assertDatabaseHas('controls', [
            'id' => $control->id,
            'name' => 'Updated Control Name',
            'status' => 'Active',
        ]);
    }

    public function test_it_only_returns_user_controls(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Control::factory()->count(3)->create(['user_id' => $user1->id]);
        Control::factory()->count(2)->create(['user_id' => $user2->id]);

        $results = $this->controlRepository->getFilteredControls($user1);

        $this->assertCount(3, $results);
        $results->each(function ($control) use ($user1) {
            $this->assertEquals($user1->id, $control->user_id);
        });
    }
}

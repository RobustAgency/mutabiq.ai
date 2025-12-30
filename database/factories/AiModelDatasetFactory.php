<?php

namespace Database\Factories;

use App\Models\AiModel;
use App\Models\Dataset;
use App\Models\Organization;
use App\Models\AiModelDataset;
use App\Models\AiModelVersion;
use App\Models\DatasetSnapshot;
use App\Enums\AiModelDataset\Role;
use App\Enums\AiModelDataset\CreatedBy;
use App\Enums\AiModelDataset\LinkageStatus;
use App\Enums\AiModelDataset\CrossBorderCheck;
use App\Enums\AiModelDataset\ConsentCheckStatus;
use App\Enums\AiModelDataset\SpecialCategoryCheck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiModelDataset>
 */
class AiModelDatasetFactory extends Factory
{
    protected $model = AiModelDataset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = fake()->randomElement(Role::cases());

        // Roles that require snapshot_id: train, validation, test, eval_benchmark
        $requiresSnapshot = in_array($role, [
            Role::TRAIN,
            Role::VALIDATION,
            Role::TEST,
            Role::EVAL_BENCHMARK,
        ]);

        return [
            'organization_id' => Organization::factory(),
            'ai_model_id' => AiModel::factory(),
            'ai_model_version_id' => AiModelVersion::factory(),
            'dataset_id' => Dataset::factory(),
            'dataset_snapshot_id' => $requiresSnapshot
                ? DatasetSnapshot::factory()
                : fake()->optional(0.3)->passthrough(DatasetSnapshot::factory()),
            'role' => $role->value,
            'rows_used' => fake()->optional()->numberBetween(100, 1000000),
            'training_start_date' => fake()->optional()->dateTimeBetween('-6 months', '-1 month'),
            'training_end_date' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'training_duration' => fake()->optional()->randomElement(['1h', '2h', '4h', '8h', '12h', '24h', '48h']),
            'compute_resources' => fake()->optional()->randomElement(['GPU-1', 'GPU-4', 'GPU-8', 'TPU-1', 'TPU-4']),
            'cost' => fake()->optional()->randomFloat(2, 10, 10000),
            'consent_check_status' => fake()->optional(0.7)->passthrough(fake()->randomElement(ConsentCheckStatus::cases())->value),
            'cross_border_check' => fake()->randomElement(CrossBorderCheck::cases())->value,
            'special_category_check' => fake()->randomElement(SpecialCategoryCheck::cases())->value,
            'bias_mitigation_applied' => fake()->boolean(),
            'created_by_system' => fake()->randomElement(CreatedBy::cases())->value,
            'linkage_status' => fake()->randomElement(LinkageStatus::cases())->value,
            'business_justification' => fake()->optional()->sentence(15),
        ];
    }

    /**
     * State for train role (requires snapshot).
     */
    public function train(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::TRAIN->value,
            'dataset_snapshot_id' => DatasetSnapshot::factory(),
        ]);
    }

    /**
     * State for validation role (requires snapshot).
     */
    public function validation(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::VALIDATION->value,
            'dataset_snapshot_id' => DatasetSnapshot::factory(),
        ]);
    }

    /**
     * State for test role (requires snapshot).
     */
    public function test(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::TEST->value,
            'dataset_snapshot_id' => DatasetSnapshot::factory(),
        ]);
    }

    /**
     * State for eval_benchmark role (requires snapshot).
     */
    public function evalBenchmark(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::EVAL_BENCHMARK->value,
            'dataset_snapshot_id' => DatasetSnapshot::factory(),
        ]);
    }

    /**
     * State without snapshot (for roles that don't require it).
     */
    public function withoutSnapshot(): static
    {
        return $this->state(fn (array $attributes) => [
            'dataset_snapshot_id' => null,
            'role' => fake()->randomElement([
                Role::PRETRAIN,
                Role::FINE_TUNE,
                Role::ALIGN_RLHF,
                Role::RAG_CORPUS,
                Role::DRIFT_BASELINE,
                Role::ONLINE_FEEDBACK,
            ])->value,
        ]);
    }

    /**
     * State with all optional fields filled.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'rows_used' => fake()->numberBetween(10000, 1000000),
            'training_start_date' => fake()->dateTimeBetween('-3 months', '-1 month'),
            'training_end_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'training_duration' => '24h',
            'compute_resources' => 'GPU-8',
            'cost' => fake()->randomFloat(2, 100, 50000),
            'consent_check_status' => ConsentCheckStatus::PASSED->value,
            'bias_mitigation_applied' => true,
            'business_justification' => fake()->sentence(20),
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Enums\AiModelDataset\EligibilityStatus;
use App\Enums\AiModelDataset\Role;
use App\Models\AiModel;
use App\Models\AiModelDataset;
use App\Models\AiModelVersion;
use App\Models\Dataset;
use App\Models\DatasetSnapshot;
use App\Models\Organization;
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
            'dataset_id' => fake()->optional(0.7)->passthrough(Dataset::factory()),
            'dataset_snapshot_id' => $requiresSnapshot
                ? DatasetSnapshot::factory()
                : fake()->optional(0.3)->passthrough(DatasetSnapshot::factory()),
            'role' => $role->value,
            'access_path' => fake()->optional()->filePath(),
            'transform_pack_link' => fake()->optional()->url(),
            'license_check_ref' => fake()->optional()->regexify('LIC-[0-9]{6}'),
            'privacy_check_ref' => fake()->optional()->regexify('PRI-[0-9]{6}'),
            'eligibility_status' => fake()->optional()->randomElement(EligibilityStatus::cases())?->value,
            'notes' => fake()->optional()->sentence(15),
        ];
    }

    /**
     * State for pretrain role.
     */
    public function pretrain(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::PRETRAIN->value,
            'dataset_snapshot_id' => fake()->optional(0.3)->passthrough(DatasetSnapshot::factory()),
        ]);
    }

    /**
     * State for train role (requires snapshot).
     */
    public function train(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::TRAIN->value,
            'dataset_snapshot_id' => DatasetSnapshot::factory(),
        ]);
    }

    /**
     * State for fine_tune role.
     */
    public function fineTune(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::FINE_TUNE->value,
            'dataset_snapshot_id' => fake()->optional(0.3)->passthrough(DatasetSnapshot::factory()),
        ]);
    }

    /**
     * State for align_rlhf role.
     */
    public function alignRlhf(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::ALIGN_RLHF->value,
            'dataset_snapshot_id' => fake()->optional(0.3)->passthrough(DatasetSnapshot::factory()),
        ]);
    }

    /**
     * State for validation role (requires snapshot).
     */
    public function validation(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::VALIDATION->value,
            'dataset_snapshot_id' => DatasetSnapshot::factory(),
        ]);
    }

    /**
     * State for test role (requires snapshot).
     */
    public function test(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::TEST->value,
            'dataset_snapshot_id' => DatasetSnapshot::factory(),
        ]);
    }

    /**
     * State for eval_benchmark role (requires snapshot).
     */
    public function evalBenchmark(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::EVAL_BENCHMARK->value,
            'dataset_snapshot_id' => DatasetSnapshot::factory(),
        ]);
    }

    /**
     * State for rag_corpus role.
     */
    public function ragCorpus(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::RAG_CORPUS->value,
            'dataset_snapshot_id' => fake()->optional(0.3)->passthrough(DatasetSnapshot::factory()),
        ]);
    }

    /**
     * State for drift_baseline role.
     */
    public function driftBaseline(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::DRIFT_BASELINE->value,
            'dataset_snapshot_id' => fake()->optional(0.3)->passthrough(DatasetSnapshot::factory()),
        ]);
    }

    /**
     * State for online_feedback role.
     */
    public function onlineFeedback(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => Role::ONLINE_FEEDBACK->value,
            'dataset_snapshot_id' => fake()->optional(0.3)->passthrough(DatasetSnapshot::factory()),
        ]);
    }

    /**
     * State for eligible status.
     */
    public function eligible(): static
    {
        return $this->state(fn(array $attributes) => [
            'eligibility_status' => EligibilityStatus::ELIGIBLE->value,
        ]);
    }

    /**
     * State for eligible with conditions status.
     */
    public function eligibleWithConditions(): static
    {
        return $this->state(fn(array $attributes) => [
            'eligibility_status' => EligibilityStatus::ELIGIBLE_WITH_CONDITIONS->value,
            'notes' => fake()->sentence(20),
        ]);
    }

    /**
     * State for not eligible status.
     */
    public function notEligible(): static
    {
        return $this->state(fn(array $attributes) => [
            'eligibility_status' => EligibilityStatus::NOT_ELIGIBLE->value,
            'notes' => fake()->sentence(20),
        ]);
    }

    /**
     * State with all optional fields filled.
     */
    public function complete(): static
    {
        return $this->state(fn(array $attributes) => [
            'dataset_id' => Dataset::factory(),
            'dataset_snapshot_id' => DatasetSnapshot::factory(),
            'access_path' => fake()->filePath(),
            'transform_pack_link' => fake()->url(),
            'license_check_ref' => fake()->regexify('LIC-[0-9]{6}'),
            'privacy_check_ref' => fake()->regexify('PRI-[0-9]{6}'),
            'eligibility_status' => fake()->randomElement(EligibilityStatus::cases())->value,
            'notes' => fake()->sentence(15),
        ]);
    }

    /**
     * State without snapshot (for roles that don't require it).
     */
    public function withoutSnapshot(): static
    {
        return $this->state(fn(array $attributes) => [
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
}

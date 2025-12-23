<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\RecordOfProcessingActivity;
use App\Enums\RecordOfProcessingActivity\Status;
use App\Enums\RecordOfProcessingActivity\OwnerTeam;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\RecordOfProcessingActivity\DPIAStatus;
use App\Enums\RecordOfProcessingActivity\LawfulBasis;
use App\Enums\RecordOfProcessingActivity\DataCategory;
use App\Enums\RecordOfProcessingActivity\ControllerRole;
use App\Enums\RecordOfProcessingActivity\DataSubjectCategory;
use App\Enums\RecordOfProcessingActivity\ApplicableJurisdiction;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecordOfProcessingActivity>
 */
class RecordOfProcessingActivityFactory extends Factory
{
    protected $model = RecordOfProcessingActivity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory();
        $uuid = Str::uuid()->toString();

        return [
            'activity_code' => 'RPA-'.$uuid,
            'activity_name' => $this->faker->sentence(3),
            'purpose' => $this->faker->sentence(),
            'detailed_purpose' => $this->faker->paragraph(),
            'owner_team' => $this->faker->randomElement(array_map(fn ($case) => $case->value, OwnerTeam::cases())),
            'controller_role' => $this->faker->randomElement(array_map(fn ($case) => $case->value, ControllerRole::cases())),
            'data_subject_categories' => [
                $this->faker->randomElement(array_map(fn ($case) => $case->value, DataSubjectCategory::cases())),
            ],
            'data_categories' => [
                $this->faker->randomElement(array_map(fn ($case) => $case->value, DataCategory::cases())),
            ],
            'contains_pii' => $this->faker->boolean(70),
            'consent_required' => $this->faker->boolean(60),
            'lawful_basis' => $this->faker->randomElement(array_map(fn ($case) => $case->value, LawfulBasis::cases())),
            'legitimate_interest_assessment' => $this->faker->boolean(50) ? $this->faker->paragraph() : null,
            'consent_coverage_percent' => $this->faker->numberBetween(0, 100),
            'dpia_required' => $this->faker->boolean(40),
            'dpia_status' => $this->faker->randomElement(array_map(fn ($case) => $case->value, DPIAStatus::cases())),
            'dpia_id' => null,
            'retention_period' => $this->faker->randomElement(['1 year', '2 years', '3 years', '5 years', '7 years']),
            'retention_justification' => $this->faker->sentence(),
            'has_international_transfers' => $this->faker->boolean(30),
            'applicable_jurisdictions' => [
                $this->faker->randomElement(array_map(fn ($case) => $case->value, ApplicableJurisdiction::cases())),
            ],
            'linked_dataset_ids' => [],
            'linked_ai_models_ids' => [],
            'security_measures' => $this->faker->sentence(),
            'internal_recipients' => [
                $this->faker->randomElement(['IT Department', 'HR Department', 'Finance Team']),
            ],
            'external_recipients' => $this->faker->boolean(50) ? [
                $this->faker->company(),
            ] : [],
            'status' => $this->faker->randomElement(array_map(fn ($case) => $case->value, Status::cases())),
            'last_reviewed_date' => $this->faker->dateTimeBetween('-3 months'),
            'next_review_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'created_by' => $user,
            'updated_by' => $user,
            'version' => 1,
        ];
    }
}

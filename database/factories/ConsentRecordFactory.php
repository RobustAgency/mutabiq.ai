<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\ConsentRecord;
use App\Enums\ConsentRecord\Method;
use App\Enums\ConsentRecord\Purpose;
use App\Enums\ConsentRecord\Language;
use App\Enums\ConsentRecord\Lifecycle;
use App\Enums\ConsentRecord\Jurisdiction;
use App\Enums\ConsentRecord\SubjectRealm;
use App\Models\RecordOfProcessingActivity;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\RecordOfProcessingActivity\DataCategory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConsentRecord>
 */
class ConsentRecordFactory extends Factory
{
    protected $model = ConsentRecord::class;

    public function definition(): array
    {
        return [
            'consent_code' => 'CNY-'.$this->faker->year.'-'.$this->faker->unique()->uuid(),
            'subject_key' => $this->faker->uuid(),
            'subject_realm' => $this->faker->randomElement(SubjectRealm::cases())->value,
            'subject_age_group' => $this->faker->randomElement(['0-13', '13-18', '18-25', '25-65', '65+']),
            'purpose' => $this->faker->randomElement(Purpose::cases())->value,
            'record_of_processing_activity_id' => RecordOfProcessingActivity::factory(),
            'status' => $this->faker->randomElement(Status::cases())->value,
            'lifecycle_stage' => $this->faker->randomElement(Lifecycle::cases())->value,
            'consent_version' => $this->faker->randomDigitNotNull(),
            'consent_text' => $this->faker->paragraph(),
            'consent_method' => $this->faker->randomElement(Method::cases())->value,
            'effective_from' => $this->faker->dateTime(),
            'effective_to' => $this->faker->optional()->dateTime(),
            'obtained_date' => $this->faker->dateTime(),
            'withdrawal_date' => $this->faker->optional()->dateTime(),
            'last_refreshed_date' => $this->faker->optional()->dateTime(),
            'source_system' => $this->faker->randomElement(['web_portal', 'mobile_app', 'api', 'backend_system']),
            'evidence_uri' => $this->faker->optional()->url(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'language' => $this->faker->randomElement(Language::cases())->value,
            'jurisdiction' => $this->faker->randomElement(Jurisdiction::cases())->value,
            'data_categories' => [
                $this->faker->randomElement(array_map(fn ($case) => $case->value, DataCategory::cases())),
            ],
            'can_withdraw' => $this->faker->boolean(),
            'withdrawal_method' => $this->faker->randomElement(['email', 'phone', 'online_form', 'api']),
        ];
    }
}

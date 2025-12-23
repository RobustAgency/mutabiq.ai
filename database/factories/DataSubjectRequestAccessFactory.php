<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\DataSubjectRequestAccess;
use App\Enums\DataSubjectRequestAccess\Status;
use App\Enums\DataSubjectRequestAccess\Priority;
use App\Enums\DataSubjectRequestAccess\RequestType;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\DataSubjectRequestAccess\SubjectRealm;
use App\Enums\DataSubjectRequestAccess\RequestSource;
use App\Enums\DataSubjectRequestAccess\ResponseFormat;
use App\Enums\DataSubjectRequestAccess\ResponseMethod;
use App\Enums\DataSubjectRequestAccess\VerificationMethod;
use App\Enums\DataSubjectRequestAccess\VerificationStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DataSubjectRequestAccess>
 */
class DataSubjectRequestAccessFactory extends Factory
{
    protected $model = DataSubjectRequestAccess::class;

    public function definition(): array
    {
        $submittedDate = $this->faker->dateTimeBetween('-90 days', 'now');
        $dueDate = $this->faker->dateTimeBetween($submittedDate, '+30 days');
        $isOverdue = now() > $dueDate;

        return [
            'request_code' => 'DSR-'.strtoupper($this->faker->unique()->bothify('????-####')),
            'request_type' => $this->faker->randomElement(RequestType::cases())->value,
            'subject_identifier' => $this->faker->email(),
            'subject_key' => $this->faker->uuid(),
            'subject_name' => $this->faker->name(),
            'subject_realm' => $this->faker->randomElement(SubjectRealm::cases())->value,
            'verification_status' => $this->faker->randomElement(VerificationStatus::cases())->value,
            'verification_method' => $this->faker->randomElement(VerificationMethod::cases())->value,
            'verification_date' => $this->faker->optional(0.6)->dateTime(),
            'verified_by' => $this->faker->optional(0.6)->randomElement(User::query()->pluck('id')->toArray()),
            'request_details' => $this->faker->sentence(),
            'requested_data_categories' => $this->faker->randomElements(
                ['personal_info', 'contact_data', 'transaction_history', 'preferences', 'communication_logs'],
                $this->faker->numberBetween(1, 3)
            ),
            'request_source' => $this->faker->randomElement(RequestSource::cases())->value,
            'submitted_date' => $submittedDate,
            'due_date' => $dueDate,
            'extended_due_date' => $this->faker->optional(0.3)->dateTimeBetween($dueDate, '+60 days'),
            'response_date' => $this->faker->optional(0.4)->dateTime(),
            'completed_date' => $this->faker->optional(0.3)->dateTime(),
            'status' => $this->faker->randomElement(Status::cases())->value,
            'priority' => $this->faker->randomElement(Priority::cases())->value,
            'is_overdue' => $isOverdue,
            'assigned_to' => User::factory(),
            'assigned_date' => $this->faker->dateTime(),
            'response_method' => $this->faker->randomElement(ResponseMethod::cases())->value,
            'response_format' => $this->faker->randomElement(ResponseFormat::cases())->value,
            'response_uri' => $this->faker->optional(0.4)->url(),
            'response_notes' => $this->faker->optional(0.4)->paragraph(),
            'rejection_reason' => $this->faker->optional(0.2)->sentence(),
            'jurisdiction' => $this->faker->randomElement(['EU', 'UAE', 'UK', 'KSA', 'DIFC']),
            'processing_activity_ids' => $this->faker->optional(0.5)->randomElements([1, 2, 3, 4, 5], $this->faker->numberBetween(1, 3)),
            'systems_checked' => $this->faker->randomElements(
                ['CRM', 'Email System', 'Marketing Database', 'Support Tickets', 'Analytics Platform'],
                $this->faker->numberBetween(1, 3)
            ),
            'records_found' => $this->faker->optional(0.7)->numberBetween(0, 500),
        ];
    }

    /**
     * State for a draft data subject request
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::NEW->value,
            'verification_status' => VerificationStatus::PENDING->value,
            'priority' => Priority::NORMAL->value,
        ]);
    }

    /**
     * State for a verified data subject request
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verification_status' => VerificationStatus::VERIFIED->value,
            'subject_key' => $this->faker->uuid(),
            'verification_method' => $this->faker->randomElement(VerificationMethod::cases())->value,
            'verified_by' => User::factory(),
            'verification_date' => $this->faker->dateTime(),
        ]);
    }

    /**
     * State for an in-progress data subject request
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::IN_PROGRESS->value,
            'verification_status' => VerificationStatus::VERIFIED->value,
            'subject_key' => $this->faker->uuid(),
            'verification_method' => $this->faker->randomElement(VerificationMethod::cases())->value,
            'verified_by' => User::factory(),
            'verification_date' => $this->faker->dateTime(),
        ]);
    }

    /**
     * State for a completed data subject request
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::COMPLETED->value,
            'verification_status' => VerificationStatus::VERIFIED->value,
            'subject_key' => $this->faker->uuid(),
            'verification_method' => $this->faker->randomElement(VerificationMethod::cases())->value,
            'verified_by' => User::factory(),
            'verification_date' => $this->faker->dateTime(),
            'response_date' => $this->faker->dateTime(),
            'completed_date' => $this->faker->dateTime(),
            'response_method' => $this->faker->randomElement(ResponseMethod::cases())->value,
            'response_format' => $this->faker->randomElement(ResponseFormat::cases())->value,
            'response_uri' => $this->faker->url(),
            'is_overdue' => false,
        ]);
    }

    /**
     * State for a high-priority data subject request
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Priority::URGENT->value,
        ]);
    }

    /**
     * State for a low-priority data subject request
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Priority::LOW->value,
        ]);
    }

    /**
     * State for an overdue data subject request
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_overdue' => true,
            'due_date' => now()->subDays($this->faker->numberBetween(1, 30)),
        ]);
    }

    /**
     * State for an access request
     */
    public function accessRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => RequestType::ACCESS->value,
        ]);
    }

    /**
     * State for an erasure request
     */
    public function erasureRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => RequestType::ERASURE->value,
        ]);
    }

    /**
     * State for a rectification request
     */
    public function rectificationRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'request_type' => RequestType::RECTIFICATION->value,
        ]);
    }
}

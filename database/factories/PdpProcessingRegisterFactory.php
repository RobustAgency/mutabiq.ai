<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Enums\UserConsent\LegalBasis;
use App\Enums\UserConsent\SubjectRealm;
use App\Models\PdpProcessingRegister;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PdpProcessingRegister>
 */
class PdpProcessingRegisterFactory extends Factory
{
    protected $model = PdpProcessingRegister::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dataSubjectCategories = $this->faker->randomElements(
            [SubjectRealm::CUSTOMER->value, SubjectRealm::PROSPECT->value, SubjectRealm::EMPLOYEE->value],
            $this->faker->numberBetween(1, 2)
        );

        $personalDataCategories = $this->faker->randomElements(
            ['Identifier', 'Contact', 'Demographic', 'Financial'],
            $this->faker->numberBetween(1, 3)
        );

        return [
            'purpose' => $this->faker->randomElement([
                'Fraud detection',
                'Service personalization',
                'Analytics',
                'AI model training',
            ]),
            'controller_role' => $this->faker->randomElement(['Controller', 'Processor', 'Joint Controller']),
            'data_subject_categories' => $dataSubjectCategories,
            'personal_data_categories' => $personalDataCategories,
            'lawful_basis' => $this->faker->randomElement(LegalBasis::cases())->value,
            'lawful_basis_detail' => $this->faker->boolean(50) ? $this->faker->sentence() : null,
            'retention_policy_ref' => $this->faker->boolean(50) ? 'RET-' . $this->faker->randomNumber(4) : null,
            'recipients' => $this->faker->boolean(40) ? ['External Processor', 'Cloud Provider'] : null,
            'international_transfer_ref' => $this->faker->boolean(30) ? 'SCC-2021' : null,
            'dpia_required_flag' => $this->faker->randomElement(['Yes', 'No', 'Pending']),
            'security_measures_ref' => $this->faker->boolean(70) ? 'SEC-' . $this->faker->randomNumber(4) : null,
            'owner_team' => $this->faker->randomElement([
                'Data Science Team',
                'Engineering Team',
                'Compliance Team',
            ]),
            'effective_from' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'effective_to' => $this->faker->boolean(20) ? $this->faker->dateTimeBetween('now', '+1 year') : null,
            'status' => $this->faker->randomElement(Status::cases())->value,
        ];
    }
}

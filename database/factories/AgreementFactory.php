<?php

namespace Database\Factories;

use App\Models\Agreement;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agreement>
 */
class AgreementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $agreementTypes = ['msa', 'dpa', 'order_form', 'addendum', 'sla', 'other'];
        $statuses = ['draft', 'active', 'lapsed', 'terminated'];
        $trainingOptOut = ['yes', 'no', 'not_applicable'];
        $auditRights = ['yes', 'no', 'limited'];
        $transferMechanisms = ['adequacy', 'sccs', 'bcrs', 'dpa_addendum', 'derogation', 'none'];

        $effectiveFrom = $this->faker->dateTimeBetween('-2 years', 'now');
        $effectiveTo = $this->faker->dateTimeBetween('now', '+3 years');

        $agreementType = $this->faker->randomElement($agreementTypes);

        // Generate SLA terms only if agreement type is 'sla'
        $slaTerms = null;
        if ($agreementType === 'sla' && $this->faker->boolean(80)) {
            $slaTerms = [
                'availability_target_pct' => $this->faker->randomFloat(2, 99.0, 99.99),
                'latency_p95_ms' => $this->faker->numberBetween(50, 500),
                'support_tier' => $this->faker->randomElement(['standard', 'premium', 'enterprise']),
                'breach_definition' => $this->faker->sentence(),
                'credit_schedule_ref' => 'SLA-CREDIT-' . $this->faker->numberBetween(1000, 9999),
                'monitoring_ref' => 'MON-' . $this->faker->numberBetween(1000, 9999),
            ];
        }

        return [
            'vendor_id' => Vendor::factory(),
            'agreement_type' => $agreementType,
            'status' => $this->faker->randomElement($statuses),
            'effective_from' => $effectiveFrom,
            'effective_to' => $effectiveTo,
            'training_opt_out' => $this->faker->boolean(70) ? $this->faker->randomElement($trainingOptOut) : null,
            'audit_rights' => $this->faker->boolean(80) ? $this->faker->randomElement($auditRights) : null,
            'transfer_mechanism' => $this->faker->boolean(75) ? $this->faker->randomElement($transferMechanisms) : null,
            'sla_terms' => $slaTerms,
            'doc_ref' => $this->faker->url() . '/agreement-' . $this->faker->uuid() . '.pdf',
        ];
    }

    /**
     * Indicate that the agreement is a DPA.
     */
    public function dpa(): static
    {
        return $this->state(fn(array $attributes) => [
            'agreement_type' => 'dpa',
        ]);
    }

    /**
     * Indicate that the agreement is an SLA with terms.
     */
    public function sla(): static
    {
        return $this->state(fn(array $attributes) => [
            'agreement_type' => 'sla',
            'sla_terms' => [
                'availability_target_pct' => 99.95,
                'latency_p95_ms' => 200,
                'support_tier' => 'premium',
                'breach_definition' => 'Service unavailable for more than 1 hour',
                'credit_schedule_ref' => 'SLA-CREDIT-2024',
                'monitoring_ref' => 'MON-2024',
            ],
        ]);
    }

    /**
     * Indicate that the agreement is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
            'effective_from' => now()->subMonth(),
            'effective_to' => now()->addYear(),
        ]);
    }

    /**
     * Indicate that the agreement is draft.
     */
    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'draft',
        ]);
    }
}

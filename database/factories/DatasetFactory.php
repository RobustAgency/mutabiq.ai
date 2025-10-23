<?php

namespace Database\Factories;

use App\Enums\Dataset\ContainsPii;
use App\Enums\Dataset\ControllerRole;
use App\Enums\Dataset\CrossBorderTransfer;
use App\Enums\Dataset\DataStructure;
use App\Enums\Dataset\DataSubjectCategory;
use App\Enums\Dataset\LawfulBasis;
use App\Enums\Dataset\LicenseType;
use App\Enums\Dataset\Purpose;
use App\Enums\Dataset\Sensitivity;
use App\Enums\Dataset\StorageFormat;
use App\Models\DataSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dataset>
 */
class DatasetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $containsPii = fake()->randomElement(ContainsPii::cases());
        $lawfulBasis = $containsPii === ContainsPii::YES ? fake()->randomElement(LawfulBasis::cases()) : null;
        $consentRequired = $lawfulBasis === LawfulBasis::CONSENT;

        return [
            'name' => fake()->words(3, true) . ' Dataset',
            'source_ids' => fake()->randomElements(
                DataSource::pluck('id')->toArray() ?: [1, 2, 3],
                fake()->numberBetween(1, 3)
            ),
            'purpose' => fake()->randomElement(Purpose::cases()),
            'schema_summary' => fake()->optional()->sentence(),
            'sensitivity' => fake()->randomElement(Sensitivity::cases()),
            'contains_pii' => $containsPii,
            'data_subject_categories' => fake()->randomElements(
                array_map(fn($c) => $c->value, DataSubjectCategory::cases()),
                fake()->numberBetween(1, 3)
            ),
            'controller_role' => fake()->randomElement(ControllerRole::cases()),
            'lawful_basis' => $lawfulBasis,
            'lawful_basis_detail' => $lawfulBasis ? fake()->optional()->sentence() : null,
            'consent_required' => $consentRequired,
            'consent_coverage_pct' => $consentRequired ? fake()->numberBetween(0, 100) : null,
            'consent_source_ref' => $consentRequired ? fake()->optional()->regexify('consent-[0-9]{4}') : null,
            'licensing_basis' => fake()->optional()->sentence(),
            'license_type' => fake()->optional()->randomElement(LicenseType::cases()),
            'privacy_notice_ref' => fake()->optional()->regexify('PN-[0-9]{4}'),
            'cross_border_transfer' => fake()->randomElement(CrossBorderTransfer::cases()),
            'data_structure' => fake()->randomElement(DataStructure::cases()),
            'storage_format' => fake()->randomElement(StorageFormat::cases()),
            'content_types' => fake()->optional()->randomElements(['text', 'image', 'video', 'audio', 'structured'], fake()->numberBetween(1, 3)),
            'retention_policy_ref' => fake()->optional()->randomElement(['policy-30d', 'policy-90d', 'policy-1y', 'policy-7y']),
            'dpia_ref' => fake()->optional()->regexify('DPIA-[0-9]{4}'),
            'aia_ref' => fake()->optional()->regexify('AIA-[0-9]{4}'),
            'owner_team' => fake()->randomElement(['Data Science', 'Engineering', 'Analytics', 'Research', 'Operations']),
            'refresh_cadence' => fake()->optional()->randomElement(['daily', 'weekly', 'monthly', 'quarterly', 'ad-hoc']),
            'quality_SLA' => fake()->optional()->randomElement(['99.9%', '99.5%', '95%', 'best-effort']),
            'catalog_asset_id' => fake()->optional()->regexify('CAT-[0-9]{6}'),
            'catalog_uri' => fake()->optional()->url(),
        ];
    }
}

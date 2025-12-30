<?php

namespace Database\Factories;

use App\Models\Dataset;
use App\Models\Organization;
use App\Models\DatasetSnapshot;
use App\Enums\DatasetSnapshot\Status;
use App\Enums\DatasetSnapshot\ApprovedBy;
use App\Enums\DatasetSnapshot\FileFormat;
use App\Enums\DatasetSnapshot\Compression;
use App\Enums\DatasetSnapshot\StorageTier;
use App\Enums\DatasetSnapshot\MaskingMethod;
use App\Enums\DatasetSnapshot\ResidencyZone;
use App\Enums\DatasetSnapshot\EncryptionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DatasetSnapshot>
 */
class DatasetSnapshotFactory extends Factory
{
    protected $model = DatasetSnapshot::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'dataset_id' => Dataset::factory(),
            'version_tag' => fake()->randomElement(['v1.0', 'v1.1', 'v2.0', 'v2.1', 'v3.0']),
            'supersedes_snapshot_id' => null,
            'description' => fake()->optional()->sentence(),
            'time_range_start' => fake()->optional()->dateTimeBetween('-1 year', '-1 month'),
            'time_range_end' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'row_count' => fake()->optional()->numberBetween(1000, 10000000),
            'file_count' => fake()->optional()->numberBetween(1, 1000),
            'total_size' => fake()->optional()->numberBetween(1024, 10737418240), // 1KB to 10GB
            'size_unit' => fake()->optional()->randomElement(['B', 'KB', 'MB', 'GB', 'TB']),
            'file_format' => fake()->randomElement(FileFormat::cases())->value,
            'pii_element_count' => fake()->optional()->numberBetween(0, 50),
            'consent_coverage_at_creation' => fake()->optional()->numberBetween(0, 100),
            'residency_zone' => fake()->randomElement(ResidencyZone::cases())->value,
            'storage_uri' => fake()->url().'/snapshots/'.fake()->regexify('[a-z0-9]{16}'),
            'storage_tier' => fake()->boolean() ? fake()->randomElement(StorageTier::cases())->value : null,
            'compression' => fake()->boolean() ? fake()->randomElement(Compression::cases())->value : null,
            'encryption_status' => fake()->randomElement(EncryptionStatus::cases())->value,
            'masking_method_applied' => fake()->boolean() ? fake()->randomElement(MaskingMethod::cases())->value : null,
            'quality_checksums' => fake()->optional()->sha256(),
            'created_by_system' => fake()->boolean(),
            'approved_by' => fake()->boolean() ? fake()->randomElement(ApprovedBy::cases())->value : null,
            'expiration_date' => fake()->optional()->dateTimeBetween('now', '+5 years'),
            'status' => fake()->randomElement(Status::cases())->value,
        ];
    }
}

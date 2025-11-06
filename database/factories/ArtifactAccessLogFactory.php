<?php

namespace Database\Factories;

use App\Enums\ArtifactAccessLog\AccessAction;
use App\Enums\ArtifactAccessLog\AccessContext;
use App\Models\AiModelArtifact;
use App\Models\ArtifactAccessLog;
use App\Models\Stakeholder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ArtifactAccessLog>
 */
class ArtifactAccessLogFactory extends Factory
{
    protected $model = ArtifactAccessLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'artifact_id' => AiModelArtifact::factory(),
            'accessor_stakeholder_id' => Stakeholder::factory(),
            'action' => fake()->randomElement(AccessAction::cases())->value,
            'context' => fake()->randomElement(AccessContext::cases())->value,
            'ts' => fake()->dateTimeBetween('-1 year', 'now'),
            'ip_or_agent' => fake()->optional()->ipv4() . ' / ' . fake()->optional()->userAgent(),
            'request_id' => fake()->optional()->uuid(),
            'reason' => fake()->optional()->sentence(),
        ];
    }
}

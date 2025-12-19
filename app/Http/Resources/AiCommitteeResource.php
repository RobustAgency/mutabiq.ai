<?php

namespace App\Http\Resources;

use App\Models\AiCommittee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AiCommittee
 */
class AiCommitteeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'charter' => $this->charter,
            'cadence' => $this->cadence,
            'owner_team' => $this->owner_team,
            'active' => $this->active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

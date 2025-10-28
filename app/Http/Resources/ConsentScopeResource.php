<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

/**
 * @mixin \App\Models\ConsentScope
 */
class ConsentScopeResource extends JsonResource
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
            'dataset_id' => $this->dataset_id,
            'purpose' => $this->purpose,
            'subject_realm' => $this->subject_realm,
            'jurisdiction' => $this->jurisdiction,
            'effective_from' => $this->effective_from ? Carbon::parse($this->effective_from)->toDateString() : null,
            'effective_to' => $this->effective_to ? Carbon::parse($this->effective_to)->toDateString() : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'dataset' => new DatasetResource($this->whenLoaded('dataset')),
        ];
    }
}

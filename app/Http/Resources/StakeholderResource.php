<?php

namespace App\Http\Resources;

use App\Models\Stakeholder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Stakeholder
 */
class StakeholderResource extends JsonResource
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
            'display_id' => $this->display_id,
            'organization_id' => $this->organization_id,
            'type' => $this->type,
            'display_name' => $this->display_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'org_unit' => $this->org_unit,
            'email' => $this->email,
            'secondary_email' => $this->secondary_email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'role_tags' => $this->role_tags,
            'timezone' => $this->timezone,
            'classification' => $this->classification,
            'country' => $this->country,
            'external_ref' => $this->external_ref,
            'employee_id' => $this->employee_id,
            'cost_center' => $this->cost_center,
            'manager' => $this->manager,
            'delegate' => $this->delegate,
            'status' => $this->status,
            'notes' => $this->notes,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}

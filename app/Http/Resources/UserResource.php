<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
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
            'email' => $this->email,
            'organization_id' => $this->organization_id,
            'is_organization_active' => $this->organization?->is_active,
            'is_super_admin' => $this->role === UserRole::SUPER_ADMIN,
            'roles' => $this->roles->pluck('name')->all(),
            'permissions' => $this->formattedPermissions(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

    private function formattedPermissions(): array
    {
        return $this->getAllPermissions()->groupBy([
            fn ($p) => explode('.', $p->name)[0], // Module
            fn ($p) => explode('.', $p->name)[1], // Screen
        ])->all();
    }
}

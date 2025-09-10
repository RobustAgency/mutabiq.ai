<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeamInviteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'members' => ['required', 'array', 'min:1'],
            'members.*.email' => ['required', 'email', 'max:255'],
            'members.*.role' => ['required', Rule::in(array_map(fn ($r) => $r->value, UserRole::cases()))],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\UserRole;

class UpdateMemberRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'role' => [
                'required',
                Rule::in(
                    array_map(
                        fn ($c) => $c->value,
                        array_filter(UserRole::cases(), fn ($c) => !in_array($c, [UserRole::ADMIN, UserRole::SUPER_ADMIN]))
                    )
                ),
            ],
        ];
    }
}

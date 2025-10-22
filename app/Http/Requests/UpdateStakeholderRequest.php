<?php

namespace App\Http\Requests;

use App\Enums\Stakeholder\Type;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStakeholderRequest extends FormRequest
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
            'type' => [
                'sometimes',
                'string',
                Rule::in(array_map(fn($type) => $type->value, Type::cases())),
            ],
            'display_name' => ['sometimes', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'org_unit' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'role_tags' => ['nullable', 'array'],
            'role_tags.*' => ['string', 'max:100'],
            'timezone' => ['sometimes', 'string', 'timezone'],
            'classification' => ['sometimes', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:500'],
            'external_ref' => ['nullable', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}

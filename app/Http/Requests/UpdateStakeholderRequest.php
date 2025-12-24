<?php

namespace App\Http\Requests;

use App\Enums\Stakeholder\Type;
use Illuminate\Validation\Rule;
use App\Enums\Stakeholder\Status;
use App\Enums\Stakeholder\Classification;
use Illuminate\Foundation\Http\FormRequest;

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
                Rule::in(array_map(fn ($type) => $type->value, Type::cases())),
            ],
            'display_name' => ['sometimes', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'org_unit' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'secondary_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'role_tags' => ['sometimes', 'array'],
            'role_tags.*' => ['string', 'max:100'],
            'timezone' => ['sometimes', 'string', 'timezone'],
            'classification' => ['sometimes', Rule::enum(Classification::class)],
            'country' => ['sometimes', 'string', 'max:500'],
            'external_ref' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:100'],
            'cost_center' => ['nullable', 'string', 'max:100'],
            'manager' => ['nullable', 'string', 'max:255'],
            'delegate' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::enum(Status::class)],
            'notes' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\Stakeholder\Type;
use Illuminate\Validation\Rule;
use App\Enums\Stakeholder\Status;
use App\Enums\Stakeholder\Classification;
use Illuminate\Foundation\Http\FormRequest;

class StoreStakeholderRequest extends FormRequest
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
                'required',
                'string',
                Rule::in(array_map(fn ($type) => $type->value, Type::cases())),
            ],
            'display_name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'org_unit' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'secondary_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'role_tags' => ['required', 'array'],
            'role_tags.*' => ['string', 'max:100'],
            'timezone' => ['required', 'string', 'timezone'],
            'classification' => ['required', Rule::enum(Classification::class)],
            'country' => ['required', 'string', 'max:500'],
            'external_ref' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:100'],
            'cost_center' => ['nullable', 'string', 'max:100'],
            'manager' => ['nullable', 'string', 'max:255'],
            'delegate' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::enum(Status::class)],
            'notes' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}

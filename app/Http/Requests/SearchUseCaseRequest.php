<?php

namespace App\Http\Requests;

use App\Enums\UseCase\Status;
use Illuminate\Validation\Rule;
use App\Enums\UseCase\BusinessDomain;
use Illuminate\Foundation\Http\FormRequest;

class SearchUseCaseRequest extends FormRequest
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
            'preliminary_risk_level' => ['sometimes', 'nullable', 'string'],
            'business_domain' => ['sometimes', 'nullable', Rule::in(array_map(fn ($c) => $c->value, BusinessDomain::cases()))],
            'owner' => ['sometimes', 'nullable', 'string'],
            'to' => ['sometimes', 'nullable', 'date'],
            'from' => ['sometimes', 'nullable', 'date', 'before_or_equal:to'],
            'status' => ['sometimes', 'string', Rule::in(array_map(fn ($c) => $c->value, Status::cases()))],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}

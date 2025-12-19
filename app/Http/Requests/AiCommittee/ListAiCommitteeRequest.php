<?php

namespace App\Http\Requests\AiCommittee;

use App\Enums\AiCommittee\Type;
use Illuminate\Validation\Rule;
use App\Enums\AiCommittee\Cadence;
use Illuminate\Foundation\Http\FormRequest;

class ListAiCommitteeRequest extends FormRequest
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
            'type' => ['nullable', Rule::enum(Type::class)],
            'cadence' => ['nullable', Rule::enum(Cadence::class)],
            'active' => ['nullable', 'boolean'],
            'name' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

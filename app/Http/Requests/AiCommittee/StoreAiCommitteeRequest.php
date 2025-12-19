<?php

namespace App\Http\Requests\AiCommittee;

use App\Enums\AiCommittee\Type;
use Illuminate\Validation\Rule;
use App\Enums\AiCommittee\Cadence;
use Illuminate\Foundation\Http\FormRequest;

class StoreAiCommitteeRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'type' => ['required', Rule::enum(Type::class)],
            'charter' => ['required', 'string'],
            'cadence' => ['required', Rule::enum(Cadence::class)],
            'owner_team' => ['required', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
        ];
    }
}

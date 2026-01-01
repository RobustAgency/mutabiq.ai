<?php

namespace App\Http\Requests\AiIncident;

use Illuminate\Validation\Rule;
use App\Enums\AiIncident\IncidentStatus;
use App\Enums\AiIncident\IncidentSeverity;
use Illuminate\Foundation\Http\FormRequest;

class ListAiIncidentRequest extends FormRequest
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
            'title' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::enum(IncidentStatus::class)],
            'severity' => ['nullable', 'string', Rule::enum(IncidentSeverity::class)],
            'from' => ['nullable', 'date', 'before_or_equal:today'],
            'to' => ['nullable', 'date', 'before_or_equal:today', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

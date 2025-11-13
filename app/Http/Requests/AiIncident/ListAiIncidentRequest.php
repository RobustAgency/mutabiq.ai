<?php

namespace App\Http\Requests\AiIncident;

use App\Enums\AiIncident\IncidentCategory;
use App\Enums\AiIncident\IncidentSeverity;
use App\Enums\AiIncident\IncidentStage;
use App\Enums\AiIncident\IncidentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'stage' => ['nullable', 'string', Rule::enum(IncidentStage::class)],
            'category' => ['nullable', 'string', Rule::enum(IncidentCategory::class)],
            'from' => ['nullable', 'date', 'before_or_equal:today'],
            'to' => ['nullable', 'date', 'before_or_equal:today', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

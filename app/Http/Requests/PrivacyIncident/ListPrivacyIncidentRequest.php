<?php

namespace App\Http\Requests\PrivacyIncident;

use Illuminate\Validation\Rule;
use App\Enums\PrivacyIncident\Status;
use App\Enums\PrivacyIncident\RiskLevel;
use App\Enums\PrivacyIncident\IncidentType;
use Illuminate\Foundation\Http\FormRequest;

class ListPrivacyIncidentRequest extends FormRequest
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
            'incident_type' => ['nullable', Rule::enum(IncidentType::class)],
            'risk_level' => ['nullable', Rule::enum(RiskLevel::class)],
            'status' => ['nullable', Rule::enum(Status::class)],
            'is_breach' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

<?php

namespace App\Http\Requests\DataProtectionImpactAssessment;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\DataProtectionImpactAssessment\Stage;
use App\Enums\DataProtectionImpactAssessment\Status;
use App\Enums\DataProtectionImpactAssessment\RiskLevel;

class ListDataProtectionImpactAssessmentRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(Status::class)],
            'stage' => ['nullable', Rule::enum(Stage::class)],
            'risk_level' => ['nullable', Rule::enum(RiskLevel::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
